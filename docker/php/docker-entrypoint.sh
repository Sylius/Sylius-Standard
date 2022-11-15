#!/bin/sh
set -e

# first arg is `-f` or `--some-option`
if [ "${1#-}" != "$1" ]; then
	set -- php-fpm "$@"
fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'bin/console' ]; then
	mkdir -p var/cache var/log var/sessions public/media
	setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var public/media
	setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var public/media

	if [ "$APP_ENV" != 'prod' ]; then
		composer install --prefer-dist --no-progress --no-interaction
		bin/console assets:install --no-interaction
		bin/console sylius:theme:assets:install public --no-interaction
	fi

	while ping -c1 migrations >/dev/null 2>&1;
	do
	    (>&2 echo "Waiting for Migrations container to finish")
	    sleep 1;
	done;
fi

exec docker-php-entrypoint "$@"
