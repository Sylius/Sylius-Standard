#!/bin/bash

# Start php-fpm
echo "Starting php-fpm8.1"
php-fpm8.1 -D -R
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start php-fpm8.1: $status"
  exit $status
fi
echo "PHP running"

# Start nginx
echo "Starting nginx"
nginx -g "daemon on;"
status=$?
if [ $status -ne 0 ]; then
  echo "Failed to start nginx: $status"
  exit $status
fi
echo "nginx running"

# Naive check runs checks once a minute to see if either of the processes exited.
# This illustrates part of the heavy lifting you need to do if you want to run
# more than one service in a container. The container exits with an error
# if it detects that either of the processes has exited.
# Otherwise it loops forever, waking up every 60 seconds

while sleep 60; do
  ps aux | grep php-fpm | grep -q -v grep
  PHP_FPM_STATUS=$?
  ps aux | grep nginx | grep -q -v grep
  NGINX_STATUS=$?
  # If the greps above find anything, they exit with 0 status
  # If they are not both 0, then something is wrong
  if [ $PHP_FPM_STATUS -ne 0 -o $NGINX_STATUS -ne 0 ]; then
    echo "One of the processes has already exited."
    exit 1
  fi
done
