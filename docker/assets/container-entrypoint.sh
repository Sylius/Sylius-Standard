#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
    set -- node "$@"
fi

if [ "$1" = 'node' ] || [ "$1" = 'yarn' ]; then
    yarn install

    >&2 echo "Waiting for Sylius Server (sylius:${PHP_FPM_WWW_LISTEN}) to be ready..."
    until nc -z "sylius" "${PHP_FPM_WWW_LISTEN}"; do
        sleep 5
    done
fi

exec "$@"
