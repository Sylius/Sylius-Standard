#!/usr/bin/env bash
#
# QA Companion pilot -- deploy this shop on its staging host (pilot step P.2).
#
# Run it on the host, from anywhere, as the account that owns the checkout:
#
#     etc/pilot/deploy.sh                     first run, and every later deploy
#     etc/pilot/deploy.sh --pull              fast-forward the checkout first
#     etc/pilot/deploy.sh --reload-fixtures   rebuild the demo world from fixtures
#
# Safe to run twice. It works out for itself whether this is a first install or a
# redeploy, because the two need different things and getting it wrong either wipes a
# database or fails on duplicate keys (P.3: loading a suite twice fails).
#
# The runbook is etc/pilot/README.md.

set -euo pipefail

cd "$(dirname "$0")/../.."

readonly ENV_FILE='etc/pilot/staging.env'
readonly OVERLAY='compose.override.staging.yml'

pull=false
reload_fixtures=false

for argument in "$@"; do
    case "$argument" in
        --pull) pull=true ;;
        --reload-fixtures) reload_fixtures=true ;;
        *) echo "Unknown option: $argument" >&2; exit 64 ;;
    esac
done

say() { printf '\n\033[1m==> %s\033[0m\n' "$1"; }
fail() { printf '\n\033[31m%s\033[0m\n' "$1" >&2; exit 1; }

[ -f "$ENV_FILE" ] || fail "$ENV_FILE does not exist. Copy etc/pilot/staging.env.dist to it and fill it in."

# Read the configuration without exporting it into every child process: only the few
# commands below that need a secret are handed one, and none of them take it as an
# argument, where it would show up in ps and in shell history.
set -a
# shellcheck disable=SC1090
. "./$ENV_FILE"
set +a

for required in PILOT_BASE_URL SYLIUS_FIXTURES_HOSTNAME APP_SECRET MYSQL_ROOT_PASSWORD; do
    value="${!required:-}"
    [ -n "$value" ] && [ "$value" != 'REPLACE-ME' ] && [ "$value" != "http://REPLACE-ME" ] \
        || fail "$required is not set in $ENV_FILE."
done

# The overlay is what makes this a staging host rather than a developer machine. A symlink
# rather than a copy, so it cannot drift from the tracked file it is supposed to be -- and
# never over a real file, because on a developer machine that file is their dev overlay and
# this script would be about to publish port 80 and reinstall their database.
if [ -e compose.override.yml ] && [ ! -L compose.override.yml ]; then
    fail 'compose.override.yml is a real file, so this looks like a developer checkout rather than the staging host. Move it aside if this host is really the pilot staging environment.'
fi

ln -sfn "$OVERLAY" compose.override.yml

readonly DC=(docker compose --env-file "$ENV_FILE")

if [ "$pull" = true ]; then
    say 'Fast-forwarding the checkout'
    git pull --ff-only
fi

say 'Pulling images'
"${DC[@]}" pull --quiet

say 'Installing PHP dependencies'
"${DC[@]}" run --rm php composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

say 'Building front-end assets'
"${DC[@]}" run --rm nodejs

say 'Starting the environment'
"${DC[@]}" up -d

say 'Waiting for PHP to answer'
for _ in $(seq 1 60); do
    if "${DC[@]}" exec -T php true 2>/dev/null; then
        break
    fi
    sleep 2
done
"${DC[@]}" exec -T php true || fail 'The php service never became usable. `docker compose logs php` will say why.'

console() { "${DC[@]}" exec -T php bin/console "$@"; }

# An installed shop has a channel; a fresh database has no tables at all. This is the one
# question that decides everything below, so it is asked of the database rather than of a
# marker file somebody could delete.
if console doctrine:query:sql 'SELECT 1 FROM sylius_channel LIMIT 1' >/dev/null 2>&1; then
    installed=true
else
    installed=false
fi

load_pilot_fixtures() {
    say 'Loading the demo world'
    console sylius:fixtures:load qa_pilot -n

    # Sylius's default fixtures recreate sylius@example.com with the password "sylius", so
    # this has to happen after every fixture load rather than once at install time.
    if [ -n "${PILOT_ADMIN_PASSWORD:-}" ] && [ "$PILOT_ADMIN_PASSWORD" != 'REPLACE-ME' ]; then
        say 'Setting the admin password'
        printf '%s\n%s\n' "${PILOT_ADMIN_EMAIL:-sylius@example.com}" "$PILOT_ADMIN_PASSWORD" \
            | console sylius:admin-user:change-password
    else
        printf '\n\033[33mPILOT_ADMIN_PASSWORD is not set: the admin account keeps Sylius'"'"'s default password.\033[0m\n'
    fi
}

if [ "$installed" = false ]; then
    say 'Installing Sylius'
    # -s default seeds the baseline the pilot suite is additive on top of, and this also
    # generates the JWT keys, which are not in the repository.
    console sylius:install -s default -n

    load_pilot_fixtures
elif [ "$reload_fixtures" = true ]; then
    say 'Rebuilding the baseline'
    # Reset by rebuilding from fixtures: slower than a snapshot restore (~20 s against ~3 s)
    # and it regenerates the random parts of the demo data, which is why P.3 makes snapshot
    # restore the reset for repeated runs and this the reset for after a fixture or hostname
    # change.
    console sylius:fixtures:load default -n

    load_pilot_fixtures
else
    say 'Applying migrations'
    console doctrine:migrations:migrate -n --allow-no-migration
fi

say 'Installing assets'
console sylius:install:assets

say 'Clearing the cache'
console cache:clear

cat <<SUMMARY

Deployed.

  Shop     $PILOT_BASE_URL
  Admin    $PILOT_BASE_URL/admin
  Mailpit  http://$SYLIUS_FIXTURES_HOSTNAME:8025   (REST API at /api/v1)

Check it from the machine running the platform, not from here -- reachability is the point:

  curl -sS -o /dev/null -w '%{http_code}\\n' $PILOT_BASE_URL
  curl -sS -o /dev/null -w '%{http_code}\\n' $PILOT_BASE_URL/products/pilot-reference-mug

A 500 on the shop with a 200 on /admin means the channel hostname does not match the URL
you asked for (P.3): check SYLIUS_FIXTURES_HOSTNAME, then redeploy with --reload-fixtures.
SUMMARY
