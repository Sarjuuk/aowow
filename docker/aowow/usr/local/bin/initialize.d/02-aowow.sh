#!/usr/bin/env sh
set -aeu
if [ "$SETUP_VERBOSE" = "1" ]; then
  set -x
fi

echo "Running aowow setup scripts ..."
cd /var/www/html  # ... aowow scripts requires it ...

composer install

# Use `config.php` environment bridge, don't need to generate interactively
# php ./aowow --db

if [ ! -d /var/www/html/setup/mpqdata/enus ]; then
  {
    echo "Error: directory 'setup/mpqdata' is empty."
    echo "Check directory mounted correctly and contains required data extracted from MPQ archives."
  } > /dev/stderr
  exit 1
fi

php ./aowow --sql
php ./aowow --build
