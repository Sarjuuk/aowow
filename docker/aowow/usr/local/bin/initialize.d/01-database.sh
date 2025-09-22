#!/usr/bin/env sh
set -aeu

echo "Creating '$MYSQL_DATABASE' DB ..."

## Database created from environment variable by mysql service on initialization:
#mysql -e "CREATE DATABASE $MYSQL_DATABASE"

mysql "$MYSQL_DATABASE" < '/var/www/html/setup/db_structure.sql'
