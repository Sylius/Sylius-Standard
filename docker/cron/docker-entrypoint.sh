#!/bin/sh
set -e

while ping -c1 migrations >/dev/null 2>&1;
do
    (>&2 echo "Waiting for Migrations container to finish")
    sleep 1;
done;

(>&2 echo "Migrations container finished. Starting Cron process.")

exec docker-php-entrypoint "$@"
