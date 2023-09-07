#!/bin/sh
set -e

attempt_left=20

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
    php bin/console sylius:fixtures:load --no-interaction
fi
