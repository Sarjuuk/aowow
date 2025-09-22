#!/usr/bin/env sh
set -aeu

echo "Running aowow setup scripts ..."
cd /var/www/html  # ... aowow scripts requires it ...

# Use `config.php` environment bridge, don't need to generate interactively
# php ./aowow --db

php ./aowow --build=demo,gems,glyphs,enchants,itemscaling,itemsets,locales,markup,pets,profiler,realmmenu,realms,searchbox,searchplugin,statistics,talentcalc,tooltips,weightpresets
php ./aowow --sql
