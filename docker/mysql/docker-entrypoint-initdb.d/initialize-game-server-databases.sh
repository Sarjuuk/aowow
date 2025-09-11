#!/usr/bin/env sh
set -aeu

if [ "$SETUP_SKIP" = "1" ]; then
  echo "Skip database initialization"
  exit 0
fi

# ... when service is just created, entrypoint launches temporary server for initialization, it's available
#     only through socket (for security reasons, so make using socket explicitly, instead of environment variable)

# Create temporary config to suppress warning "using password as argument is insecure"
cat << EOF > /tmp/.my.cnf
[mysql]
user=$DB_AOWOW_USER
password=$DB_AOWOW_PASS
EOF

# When maridb is used, running `mysql` will trigger an error, make fix of it to keep logs clean
if [ -f /usr/bin/mariadb ]; then
  MYSQL_BIN='/usr/bin/mariadb'
else
  MYSQL_BIN=$(which mysql)
fi

# shellcheck disable=SC2139
alias mysql="$MYSQL_BIN --defaults-file=/tmp/.my.cnf"

cd /usr/local/share/mysql

echo "Downloading database dumps ..."
WORLD_DATABASE_DUMP_FILENAME=$(basename "$SETUP_WORLD_DATABASE_DUMP_URL")  # e.g. TDB_full_world_335.21101_2021_10_15.7z
#curl -L --no-progress-meter --parallel -C - \
curl -L -S -C - \
  "$SETUP_CHARACTERS_DATABASE_DUMP_URL" -o 'characters_database.sql' \
  "$SETUP_AUTH_DATABASE_DUMP_URL" -o 'auth_database.sql' \
  "$SETUP_WORLD_DATABASE_DUMP_URL" -o "$WORLD_DATABASE_DUMP_FILENAME"

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
