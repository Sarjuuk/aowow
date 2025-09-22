#!/usr/bin/env sh
set -aeu

# This executable is running on container first start.
# If initialization script will fail, it's recommended to recreate all containers.

. /usr/local/bin/initialize.d/01-database.sh
. /usr/local/bin/initialize.d/02-assets.sh
. /usr/local/bin/initialize.d/03-aowow.sh

# todo consider to use table prefix: ${DB_AOWOW_PREFIX} ?

cat << 'SQL' | mysql "$MYSQL_DATABASE"
UPDATE aowow_config SET value='0' WHERE `key`='maintenance';
UPDATE aowow_config SET value='0' WHERE `key`='debug';
-- UPDATE aowow_config SET value='3' WHERE `key`='debug';
UPDATE aowow_config SET value='1' WHERE `key`='locales';  -- EN locale
SQL
