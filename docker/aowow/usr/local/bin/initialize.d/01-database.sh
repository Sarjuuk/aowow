#!/usr/bin/env sh
set -aeu

echo "Creating '$DB_AOWOW_DB' DB ..."
mysql -e "CREATE DATABASE $DB_AOWOW_DB"

cat '/var/www/html/setup/sql/01-db_structure.sql' \
    '/var/www/html/setup/sql/02-db_initial_data.sql' \
    '/var/www/html/setup/sql/03-db_initial_articles.sql' \
| mysql "$DB_AOWOW_DB"

echo "Done"
