#!/usr/bin/env sh
set -aeu

# This executable is running on container start.
# If container started for first time, it will run initialization scripts first.
# If initialization script will fail, it's recommended to recreate all containers.

if [ ! -f '/.aowow.initialized' ]; then  # todo consider to use more explicit attribute
  /usr/local/bin/initialize.sh;
  touch '/.aowow.initialized'
fi

cat << SQL | mysql "$MYSQL_DATABASE"
UPDATE aowow_config SET value='${HTTP_HOST}:${HTTP_PORT}' WHERE key = 'site_host';
UPDATE aowow_config SET value='${HTTP_HOST}:${HTTP_PORT}/static' WHERE key = 'static_host';
SQL

'apache2-foreground';
