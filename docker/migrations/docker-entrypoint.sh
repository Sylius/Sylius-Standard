#!/bin/sh
set -e

attempt_left=20

# dump autoload in entrypoint lso due to weird build cache issues
composer dump-autoload --classmap-authoritative --optimize

until php bin/console doctrine:query:sql "select 1" >/dev/null 2>&1;
do
    attempt_left=$((attempt_left-1))

    if [ "${attempt_left}" -eq "0" ]; then

        (>&2 echo "MySQL did not answer. Aborting migrations.")
        exit 1
    else
        (>&2 echo "Waiting for MySQL to be ready...")
    fi

    sleep 1
done

php bin/console doctrine:migrations:migrate --no-interaction

if [ "$LOAD_FIXTURES" = "1" ]; then
    # Replace localhost with BASE_DOMAIN in fixtures.yml
    if [ -z "$BASE_DOMAIN" ]; then
        sed -i "s/localhost/$BASE_DOMAIN/g" vendor/sylius/sylius/src/Sylius/Bundle/CoreBundle/Resources/config/app/fixtures.yml
    fi

    php bin/console sylius:fixtures:load --no-interaction

    # generate image cache
    find public/media/image -type f -print0 | sed 's/public\/media\/image\///' | xargs -0 -I{} sh -c 'bin/console liip:imagine:cache:resolve {} || true'
fi
