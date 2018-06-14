#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- node "$@"
fi

if [ "$1" = 'node' ] || [ "$1" = 'yarn' ]; then
	if [ "$GULP_ENV" != 'prod' ]; then
		yarn install
		yarn build
	fi
fi

exec "$@"
