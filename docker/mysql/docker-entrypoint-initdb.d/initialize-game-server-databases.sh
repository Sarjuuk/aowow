#!/usr/bin/env sh
set -aeu
if [ "$SETUP_VERBOSE" = "1" ]; then
  set -x
fi

if [ "$SETUP_SKIP" = "1" ]; then
  echo "Skip database initialization"
  exit 0
fi

# ... when service is just created, entrypoint launches temporary server for initialization, it's available
#     only through socket (for security reasons, so make using socket explicitly, instead of environment variable)

# Create temporary config to suppress warning "using password as argument is insecure"
cat << EOF > /tmp/.my.cnf
[mysql]
user=root
password=$MYSQL_ROOT_PASSWORD
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
WORLD_DATABASE_DUMP_FILENAME=$(basename "$SETUP_WORLD_DATABASE_DUMP_URL")  # e.g. TDB_full_1200.26021_2026_02_06.7z

# Download database dumps files
curl -L -S -C - "$SETUP_WORLD_DATABASE_DUMP_URL" -o "$WORLD_DATABASE_DUMP_FILENAME" &

# Download world database patches files
if [ ! -d "/usr/local/share/mysql/trinitycore-sparse" ]; then
  {
    GIT_TERMINAL_PROMPT=0 git clone --depth 1 --filter=blob:none --sparse --branch "3.3.5" \
      "$SETUP_TC_REPOSITORY" \
      "/usr/local/share/mysql/trinitycore-sparse" || exit 1
    git -C "/usr/local/share/mysql/trinitycore-sparse" \
      sparse-checkout set "sql/base" "sql/updates" || exit 1
  } &
fi
wait

echo "Creating databases ..."
cat << SQL | mysql
CREATE DATABASE $DB_WORLD_DB;
CREATE DATABASE $DB_AUTH_DB;
CREATE DATABASE $DB_CHARACTERS_1_DB;
SQL

echo "Applying database dumps ..."
mysql "$DB_AUTH_DB" < "/usr/local/share/mysql/trinitycore-sparse/sql/base/auth_database.sql" &
mysql "$DB_CHARACTERS_1_DB" < "/usr/local/share/mysql/trinitycore-sparse/sql/base/characters_database.sql" &
{
  7za x "$WORLD_DATABASE_DUMP_FILENAME" -so | mysql "$DB_WORLD_DB"
  echo "Applying database patches ..."

  # Official patches
  if [ -n "$SETUP_WORLD_DATABASE_LATEST_PATCH_FILENAME" ]; then
    find "/usr/local/share/mysql/trinitycore-sparse/sql/updates/world/3.3.5" -type f -iname '*.sql' | sort | while read -r REPLY; do
      echo "Applying $(basename "$REPLY")"
      mysql "$DB_WORLD_DB" < "$REPLY";
      #if [[ "$REPLY" == *"$SETUP_WORLD_DATABASE_LATEST_PATCH_FILENAME" ]]; then
      if printf '%s' "$REPLY" | grep -qF -- "$SETUP_WORLD_DATABASE_LATEST_PATCH_FILENAME"; then
        break;
      fi
    done
  fi

  # Unofficial patches
  # According to https://github.com/MariaDB/mariadb-docker/blob/master/12.2/docker-entrypoint.sh#L65
  # files from nested directory will not be executed. Such kind of hack is required to avoid mounting additional directory.
  find "/docker-entrypoint-initdb.d/patch/db-world" -type f -iname '*.sql' | sort | while read -r REPLY; do
    echo "Applying $(basename "$REPLY")"
    mysql "$DB_WORLD_DB" < "$REPLY";
  done
} &
wait
rm -f /tmp/.my.cnf
