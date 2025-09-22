#!/usr/bin/env sh
set -aeu

# ... when service is just created, entrypoint launches temporary server for initialization, it's available
#     only through socket (for security reasons, so make using socket explicitly, instead of environment variable)
# fixme for some reason `mariadb` doesn't uses `user name` setting from environment variable implicitly ...
# shellcheck disable=SC2139
alias mysql="MYSQL_HOST= mariadb -S /run/mysqld/mysqld.sock -u${MYSQL_USER}"

cd /usr/local/share/mysql

echo "Downloading database dumps ..."
WORLD_DATABASE_DUMP_FILENAME=$(basename "$WORLD_DATABASE_DUMP_URL")  # e.g. TDB_full_world_335.21101_2021_10_15.7z
curl -L --no-progress-meter --parallel -C - \
  "$CHARACTERS_DATABASE_DUMP_URL" -o 'characters_database.sql' \
  "$AUTH_DATABASE_DUMP_URL" -o 'auth_database.sql' \
  "$WORLD_DATABASE_DUMP_URL" -o "$WORLD_DATABASE_DUMP_FILENAME"

echo "Creating databases ..."
cat << SQL | mysql
CREATE DATABASE $DB_WORLD_DB;
CREATE DATABASE $DB_AUTH_DB;
CREATE DATABASE $DB_CHARACTERS_1_DB;
SQL

echo "Applying database dumps ..."
mysql "$DB_AUTH_DB" < "auth_database.sql" &
mysql "$DB_CHARACTERS_1_DB" < "characters_database.sql" &
7za x "$WORLD_DATABASE_DUMP_FILENAME" -so | mysql "$DB_WORLD_DB" &

wait
