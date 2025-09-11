#!/usr/bin/env sh
set -aeu
if [ "$SETUP_VERBOSE" = "1" ]; then
  set -x
fi

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

initialized() {
  # ... Database may be created on database server start, if `MYSQL_DATABASE` environment variable is set,
  #     don't check is application database exists, but tables count there
  RESULT=$(mysql -N -s -e "select count(*) > 0 from INFORMATION_SCHEMA.TABLES where TABLE_SCHEMA = '$DB_AOWOW_DB';")
  if [ -n "$RESULT" ] && [ "$RESULT" = "0" ]; then
    return 1  # no, exit code = 1 (error)
  fi
  return 0  # yes, exit code = 0 (ok)
}

# Set PHP `date.timezone` setting according to environment `PHP_DATE_TIMEZONE` variable
mkdir -p /usr/local/etc/php/tmp.conf.d
echo "date.timezone = ${PHP_DATE_TIMEZONE:-UTC}" > /usr/local/etc/php/tmp.conf.d/99-timezone.ini
export PHP_INI_SCAN_DIR="$PHP_INI_SCAN_DIR:/usr/local/etc/php/tmp.conf.d"

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
