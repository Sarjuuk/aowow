#!/usr/bin/env sh
set -aeu

# This executable is running on container start.
# If container started for first time, it will run initialization scripts first:
# 1. creates application database,
# 2. applies SQL dumps:
#    - crates database tables
#    - fills tables with data
# 3. loads prepared MPQ archive data (if was not provided)
# 4. launches application setup script
#
# Notice: set environment variable `SKIP_SETUP` to `1` to skip initialization script run.
# Notice: If initialization script will fail, it's recommended to recreate all containers.

initialized() {  # todo consider to use more explicit attribute
  # ... just check is application database exists
  RESULT=$(mysql -N -s -e "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$DB_AOWOW_DB';")
  if [ -n "$RESULT" ] && [ "$RESULT" = "$DB_AOWOW_DB" ]; then
    return 0  # yes, exit code = 0 (ok)
  fi
  return 1  # no, exit code = 1 (error)
}

if [ "$SETUP_SKIP" = "0" ] && ! initialized; then
  /usr/local/bin/initialize.sh;
fi

# Variables `HTTP_HOST`, `HTTP_PORT` may be changed between container run.
# Force update settings everytime to make application working correctly.
cat << SQL | mysql "$DB_AOWOW_DB"
UPDATE ${DB_AOWOW_PREFIX}config SET value='${HTTP_HOST}:${HTTP_PORT}' WHERE \`key\` = 'site_host';
UPDATE ${DB_AOWOW_PREFIX}config SET value='${HTTP_HOST}:${HTTP_PORT}/static' WHERE \`key\` = 'static_host';
SQL

# ... finally, run web-server
'apache2-foreground';
