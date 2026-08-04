#!/usr/bin/env bash
#
# QA Companion pilot -- bring a fresh Ubuntu 24.04 host to the point where etc/pilot/deploy.sh
# can run (pilot step P.2).
#
# Normally this runs from cloud-init at first boot (etc/pilot/cloud-init.yaml). Run it by hand
# on a host that was created without that user data -- which cannot be added afterwards on any
# provider worth using -- or after a rebuild:
#
#     curl -fsSL https://raw.githubusercontent.com/qacompanion/Sylius-Standard/pilot/etc/pilot/bootstrap.sh -o /tmp/bootstrap.sh
#     sudo bash /tmp/bootstrap.sh
#
# Safe to run again: every step checks whether it has already been done.
#
# What it does, and why:
#
#   - Docker from Docker's own repository, because the distribution packages lag and ship no
#     compose plugin.
#   - An unprivileged `pilot` account to own the checkout, inheriting root's authorised keys.
#     The containers run as this user, so what they create in the checkout belongs to it.
#   - SSH keys only, no passwords, and fail2ban -- the host answers the whole internet on 22,
#     80 and 8025 (see etc/pilot/README.md), so there must be no password to guess.
#   - 2 GB of swap, because the front-end build is the one memory-hungry step and a 4 GB host
#     has no headroom for it.

set -euo pipefail

readonly PILOT_USER='pilot'

[ "$(id -u)" -eq 0 ] || { echo 'Run this as root: sudo bash bootstrap.sh' >&2; exit 1; }

say() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }

export DEBIAN_FRONTEND=noninteractive

say 'Installing packages'
apt-get update
apt-get install -y ca-certificates curl git fail2ban

if ! command -v docker >/dev/null; then
    say 'Installing Docker'
    install -m 0755 -d /etc/apt/keyrings
    curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
    chmod a+r /etc/apt/keyrings/docker.asc
    echo "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu $(. /etc/os-release && echo "$VERSION_CODENAME") stable" \
        > /etc/apt/sources.list.d/docker.list
    apt-get update
    apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
else
    say 'Docker is already installed'
fi

say 'Refusing SSH passwords'
cat > /etc/ssh/sshd_config.d/90-pilot.conf <<'CONFIG'
PasswordAuthentication no
KbdInteractiveAuthentication no
PermitRootLogin prohibit-password
CONFIG
systemctl restart ssh

say 'Watching for brute force'
# Ubuntu 24.04 has no /var/log/auth.log -- only the journal -- and fail2ban's Debian defaults
# read the file, so its sshd jail silently never starts without this.
cat > /etc/fail2ban/jail.local <<'CONFIG'
[sshd]
enabled = true
backend = systemd
CONFIG
systemctl restart fail2ban

if id "$PILOT_USER" >/dev/null 2>&1; then
    say "The $PILOT_USER account already exists"
else
    say "Creating the $PILOT_USER account"
    useradd -m -s /bin/bash "$PILOT_USER"
fi

usermod -aG docker,sudo "$PILOT_USER"
echo "$PILOT_USER ALL=(ALL) NOPASSWD:ALL" > "/etc/sudoers.d/90-$PILOT_USER"
chmod 440 "/etc/sudoers.d/90-$PILOT_USER"

# The provider put your key in root's authorized_keys; this is how the unprivileged account
# gets it without you pasting it a second time.
if [ -f /root/.ssh/authorized_keys ]; then
    pilot_home="$(getent passwd "$PILOT_USER" | cut -d: -f6)"
    install -d -m 700 -o "$PILOT_USER" -g "$PILOT_USER" "$pilot_home/.ssh"
    install -m 600 -o "$PILOT_USER" -g "$PILOT_USER" /root/.ssh/authorized_keys "$pilot_home/.ssh/authorized_keys"
else
    printf '\n\033[33mroot has no authorized_keys, so %s got no key: add one before logging out.\033[0m\n' "$PILOT_USER"
fi

if [ -f /swapfile ]; then
    say 'Swap is already configured'
else
    say 'Adding swap'
    fallocate -l 2G /swapfile
    chmod 600 /swapfile
    mkswap /swapfile
    swapon /swapfile
    echo '/swapfile none swap sw 0 0' >> /etc/fstab
fi

cat <<SUMMARY

Ready. Log in as $PILOT_USER and deploy:

  ssh $PILOT_USER@<this host>
  git clone -b pilot https://github.com/qacompanion/Sylius-Standard.git pilot && cd pilot
  cp etc/pilot/staging.env.dist etc/pilot/staging.env && chmod 600 etc/pilot/staging.env
  \$EDITOR etc/pilot/staging.env
  etc/pilot/deploy.sh

DOCKER_USER for that file is $(id -u "$PILOT_USER"):$(id -g "$PILOT_USER").
SUMMARY
