#!/usr/bin/env sh
set -aeu

# This executable is running on container first start.
# If initialization script will fail, it's recommended to recreate all containers.

. /usr/local/bin/initialize.d/01-database.sh
. /usr/local/bin/initialize.d/02-assets.sh
. /usr/local/bin/initialize.d/03-aowow.sh

cat << SQL | mysql "$MYSQL_DATABASE"
UPDATE ${DB_AOWOW_PREFIX}config SET value='0' WHERE \`key\`='maintenance';
UPDATE ${DB_AOWOW_PREFIX}config SET value='0' WHERE \`key\`='debug';
-- UPDATE ${DB_AOWOW_PREFIX}config SET value='3' WHERE \`key\`='debug';
UPDATE ${DB_AOWOW_PREFIX}config SET value='1' WHERE \`key\`='locales';  -- EN locale
SQL
