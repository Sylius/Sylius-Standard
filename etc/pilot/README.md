# The pilot's staging host

This is pilot step **P.2** of the QA Companion roadmap: the shop runs on a host the platform
reaches over the network, not beside it on a developer's machine.

That difference is the whole point. Every provisioning command P.3 verified was
`docker compose exec php bin/console …` against a local stack, and a `ProvisionerInterface`
designed against that shape can only ever provision a shop running next to the platform —
which no customer's staging environment ever does. With the shop on its own host, the
provisioner has to send commands somewhere, which is what Phase 3.4 needs to be designed
against.

## What the host needs

- **2 vCPU, 4 GB RAM, 40 GB disk.** The memory is for `yarn build`; MySQL and PHP are
  comfortable in far less. Ubuntu 24.04 is what this was written against.
- **Docker Engine with the Compose plugin, and git.** Nothing else — no PHP, no MySQL, no
  web server on the host itself.
- **A hostname**, because Sylius resolves its channel from the HTTP `Host` header (P.3), so
  the shop only answers on the name it was told. With no domain to hand,
  `http://203-0-113-9.nip.io` resolves to `203.0.113.9` with no DNS setup at all. A real A
  record is nicer if you have a domain.
- **A firewall allowing 22, 80 and 8025, from anywhere.** Restricting those to one address
  was the first instinct and the wrong trade: the platform runs on a laptop whose address
  changes and which moves, and a rule that has to be edited before every session is a rule
  that will be found stale at the worst moment. What is behind those ports is a shop full of
  generated fixtures and a mail catcher full of test mail — losing either costs nothing.
  Everything else stays closed, and the host is hardened instead: SSH takes keys only, no
  account has a password, and fail2ban watches the journal (`etc/pilot/cloud-init.yaml`).

  What that trade does *not* cover is the admin UI, which is served over plain HTTP: signing
  in sends the password across whatever network you are on. Do that from somewhere you
  trust, and keep it a password used for nothing else.

### On an ARM host

Every image in this stack has a native `arm64` build, so an Ampere or Graviton machine is a
fine home for it — but set `MYSQL_PLATFORM=linux/arm64` in `staging.env`. Upstream's
`compose.yml` pins MySQL to `linux/amd64` (Apple Silicon developers need it), and left alone
that pin runs the database under emulation.

### Where this runs

A Hetzner **CX22** (2 vCPU, 4 GB, 40 GB, about €4/month with the IPv4 address) in
Nuremberg, Falkenstein or Helsinki. Their Ubuntu images ship no restrictive firewall rules
of their own, so the cloud firewall in the console is the only layer to get right — which
is one fewer thing to debug than it sounds.

`etc/pilot/bootstrap.sh` is what makes a fresh host into a pilot host: Docker from Docker's
own repository, an unprivileged `pilot` account inheriting the SSH key, SSH passwords
refused, fail2ban, and 2 GB of swap for the front-end build. `etc/pilot/cloud-init.yaml` is
user-data that does nothing but fetch and run it, because user data cannot be added to a
server after it is created — so a host built without it is fixed by running the script,
rather than by rebuilding:

```bash
curl -fsSL https://raw.githubusercontent.com/qacompanion/Sylius-Standard/pilot/etc/pilot/bootstrap.sh -o /tmp/bootstrap.sh
sudo bash /tmp/bootstrap.sh
```

Oracle Cloud's Always Free Ampere tier would cost nothing and is technically a fine fit —
every image here has a native arm64 build — but A1 capacity was unobtainable in practice
("Out of host capacity" on every attempt), and Oracle reclaims Always Free compute that sits
idle, which a staging environment between milestones will. Worth another look only if the
hosting bill ever matters.

## First deploy

On the host, as a non-root account that can run docker:

```bash
git clone -b pilot https://github.com/qacompanion/Sylius-Standard.git pilot
cd pilot
cp etc/pilot/staging.env.dist etc/pilot/staging.env
chmod 600 etc/pilot/staging.env
$EDITOR etc/pilot/staging.env      # every REPLACE-ME; openssl rand -hex 32 for the random two
etc/pilot/deploy.sh
```

`deploy.sh` installs dependencies, builds assets, starts the stack, installs Sylius with its
`default` fixtures, and loads the `qa_pilot` suite on top. It then undoes what Sylius's own
fixtures leave behind on a public host: the `api@example.com` account, which carries API
access and a published password, is deleted, and the remaining admin's password is changed
away from the published default. Both happen on every fixture load, because every fixture
load recreates them. It is safe to run again: it asks the database whether the shop
is installed and does migrations instead of a reinstall when it is.

## Verifying it, from the machine running the platform

Reachability is what P.2 is about, so check it from there rather than from the host:

```bash
curl -sSL -o /dev/null -w '%{http_code}\n' http://<host>                                    # 200
curl -sSL -o /dev/null -w '%{http_code}\n' http://<host>/en_US/products/pilot-reference-mug # 200
curl -sSL -o /dev/null -w '%{http_code}\n' http://<host>/admin                              # 200
curl -sS http://<host>:8025/api/v1/messages | head -c 80                                    # JSON
```

Follow redirects: the shop sends `/` to a locale prefix and `/admin` to its login page, so
without `-L` the first two answer 302 rather than 200 and look broken when they are not.

The demo data to expect is in the roadmap's P.3 and P.4 findings: customer groups `vip` and
`standard`, customers `vip@pilot.test` and `standard@pilot.test` (fixture password
`pilot123`), and product `PILOT_MUG` pinned at 2500 so a scenario can assert `$25.00`.

## Provisioning and reset

These are the commands Phase 3.4's CLI-adapter provisioner will run over SSH. They are here
because the runbook is where they were verified, not because a person should be running them
once 3.4 exists.

```bash
# Provision the demo world (additive; ~2 s). Loading the same suite twice fails on duplicate
# keys, so re-provisioning means reset-then-load, never load-again.
docker compose --env-file etc/pilot/staging.env exec php bin/console sylius:fixtures:load qa_pilot -n

# Snapshot, and restore from it: the reset for repeated runs (~1 s / ~3 s, byte-exact).
# The password stays inside the container rather than on a command line.
docker compose --env-file etc/pilot/staging.env exec -T mysql \
    sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction --routines sylius' > etc/pilot/snapshot.sql
docker compose --env-file etc/pilot/staging.env exec -T mysql \
    sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" sylius' < etc/pilot/snapshot.sql

# Rebuild the baseline from fixtures instead (~20 s, regenerates the random demo data).
# For after a fixture or hostname change, not for between runs.
etc/pilot/deploy.sh --reload-fixtures
```

Measured on this host: snapshot 0.5 s, restore 4 s, and a price tampered with in between came
back exactly as it was — the same character P.3 found in dev.

One trap for anything that scripts these, including Phase 3.4's provisioner: `docker compose
exec -T` **reads standard input**, so a command run from a script that is itself arriving on
stdin — `ssh host bash -s < script.sh` — eats the rest of the script and the run stops halfway
with no error at all. Redirect from `/dev/null` on every call that is not deliberately being
fed something, which is what `deploy.sh` does.

## Redeploying after a change to the fork

```bash
etc/pilot/deploy.sh --pull
```

Add `--reload-fixtures` when the change was to the fixtures or to
`SYLIUS_FIXTURES_HOSTNAME` — the channel hostname is stored in the database, so changing it
in configuration alone leaves the shop answering on the old name.

## When it is wrong

- **500 from the shop while `/admin` returns 200** — the channel hostname does not match the
  URL you asked for. Fix `SYLIUS_FIXTURES_HOSTNAME`, then
  `etc/pilot/deploy.sh --reload-fixtures`.
- **A service never turns healthy** — `docker compose --env-file etc/pilot/staging.env logs
  php` (or `mysql`).
- **Fixture loading fails on a missing class** — dependencies are installed with `--no-dev`,
  and something in the fixture path expects a dev-only package. Re-run the install step
  without that flag:
  `docker compose --env-file etc/pilot/staging.env run --rm php composer install --optimize-autoloader --no-interaction --no-scripts`.

## What this host is not

Not production, and not somewhere to put real data: reachable by anyone who finds it, HTTP
only, root MySQL access from inside its own network, and fixtures that recreate a known
admin account on every load. It is a stand-in for a customer's staging environment, and the
platform must not learn anything about it beyond a base URL, a mail endpoint and the names
of some credentials.
