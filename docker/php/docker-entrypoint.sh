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

	if [ "$APP_ENV" = 'dev' ]; then
		composer install --prefer-dist --no-interaction --no-scripts --no-progress
		bin/console assets:install --no-interaction
		bin/console sylius:theme:assets:install public --no-interaction
	fi

	until bin/console doctrine:query:sql "select 1" >/dev/null 2>&1; do
	    (>&2 echo "Waiting for MySQL to be ready...")
		sleep 1
	done

    bin/console doctrine:migrations:migrate --no-interaction

    if [ "$APP_ENV" = 'dev' ] || [ "$APP_ENV" = 'prod' ]; then
        bin/console sylius:fixtures:load --no-interaction
    fi
fi

exec docker-php-entrypoint "$@"
