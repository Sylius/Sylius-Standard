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
- **A firewall that allows only what has to reach it**: 22 (you), 80 (the platform's browser
  worker and you), 8025 (the platform, to read captured mail). Everything else closed. This
  matters more than usual here: it is a shop with seeded data, an admin UI and no HTTPS.
  While the platform runs on a developer machine, the address to allow is that machine's,
  which for most home connections changes from time to time — a shop that suddenly stops
  answering is worth checking against the firewall rule before anything else.

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
`default` fixtures, loads the `qa_pilot` suite on top, and changes the admin password away
from Sylius's public default. It is safe to run again: it asks the database whether the shop
is installed and does migrations instead of a reinstall when it is.

## Verifying it, from the machine running the platform

Reachability is what P.2 is about, so check it from there rather than from the host:

```bash
curl -sS -o /dev/null -w '%{http_code}\n' http://<host>                              # 200
curl -sS -o /dev/null -w '%{http_code}\n' http://<host>/products/pilot-reference-mug # 200
curl -sS -o /dev/null -w '%{http_code}\n' http://<host>/admin                        # 200
curl -sS http://<host>:8025/api/v1/messages | head -c 80                             # JSON
```

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

Not production, and not somewhere to put real data: HTTP only, root MySQL access from
inside its own network, fixtures that recreate a known admin account on every load. It is a
stand-in for a customer's staging environment, and the platform must not learn anything
about it beyond a base URL, a mail endpoint and the names of some credentials.
