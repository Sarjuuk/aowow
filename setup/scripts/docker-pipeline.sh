echo "Downloading and importing DB"
wget https://github.com/TrinityCore/TrinityCore/releases/download/TDB335.21101/TDB_full_world_335.21101_2021_10_15.7z > /dev/null 2>&1
7z x TDB_full_world_335.21101_2021_10_15.7z  > /dev/null 2>&1
rm TDB_full_world_335.21101_2021_10_15.7z

echo "creating trinitycore_world db"
mysql -u root -proot -e "CREATE DATABASE trinitycore_world;"
mysql -u root -proot -e "CREATE DATABASE trinitycore_auth;"
mysql -u root -proot -e "CREATE DATABASE trinitycore_characters;"

wget https://raw.githubusercontent.com/TrinityCore/TrinityCore/refs/heads/3.3.5/sql/base/auth_database.sql > /dev/null 2>&1
wget https://raw.githubusercontent.com/TrinityCore/TrinityCore/refs/heads/3.3.5/sql/base/characters_database.sql > /dev/null 2>&1
mysql -u root -proot trinitycore_auth < "auth_database.sql"
mysql -u root -proot trinitycore_characters < "characters_database.sql"
rm auth_database.sql
rm characters_database.sql

echo "importing TDB (it will take time)"
mysql -u root -proot trinitycore_world < "TDB_full_world_335.21101_2021_10_15.sql"
echo "TDB imported"
rm TDB_full_world_335.21101_2021_10_15.sql

echo "Creating aowow DB"
mysql -u root -proot -e "CREATE DATABASE aowow;"
mysql -u root -proot aowow < setup/db_structure.sql

mysql -u root -proot aowow -e "SET GLOBAL range_optimizer_max_mem_size=0;"
mysql -u root -proot aowow -e "UPDATE aowow_config SET value='127.0.0.1:80' WHERE \`key\`='site_host';"
mysql -u root -proot aowow -e "UPDATE aowow_config SET value='127.0.0.1:80/static' WHERE \`key\`='static_host';"
mysql -u root -proot aowow -e "UPDATE aowow_config SET value='3' WHERE \`key\`='debug';"
mysql -u root -proot aowow -e "UPDATE aowow_config SET value='1' WHERE \`key\`='locales';" # EN locale

echo "
<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


\$AoWoWconf['aowow'] = array (
  'host' => 'localhost:3306',
  'user' => 'root',
  'pass' => 'root',
  'db' => 'aowow',
  'prefix' => 'aowow_',
);

\$AoWoWconf['world'] = array (
  'host' => 'localhost:3306',
  'user' => 'root',
  'pass' => 'root',
  'db' => 'trinitycore_world',
  'prefix' => '',
);

\$AoWoWconf['auth'] = array (
  'host' => 'localhost:3306',
  'user' => 'root',
  'pass' => 'root',
  'db' => 'trinitycore_auth',
  'prefix' => '',
);

\$AoWoWconf['characters']['1'] = array (
  'host' => 'localhost:3306',
  'user' => 'root',
  'pass' => 'root',
  'db' => 'trinitycore_characters',
  'prefix' => '',
);

?>
" > config/config.php

echo "Downloading data"
mkdir -p setup/mpqdata/enus/DBFilesClient/
wget https://github.com/wowgaming/client-data/releases/download/v16/data.zip > /dev/null 2>&1
unzip data.zip "dbc/*" -d ./ > /dev/null 2>&1
mv dbc/* "setup/mpqdata/enus/DBFilesClient/"
rm data.zip

mkdir -p setup/mpqdata/interface/framexml/
wget https://raw.githubusercontent.com/wowgaming/3.3.5-interface-files/refs/heads/main/GlobalStrings.lua -O setup/mpqdata/interface/framexml/globalstrings.lua

mkdir -p setup/mpqdata/enUS/interface/framexml/
cp setup/mpqdata/interface/framexml/globalstrings.lua setup/mpqdata/enUS/interface/framexml/globalstrings.lua

php -m | grep intl
phpdismod intl
php -m | grep intl

echo "[mysqld]" >> /etc/mysql/my.cnf
echo "skip-grant-tables" >> /etc/mysql/my.cnf
echo "skip-networking" >> /etc/mysql/my.cnf

service mysql restart

mysql -u root -proot aowow -e "SET GLOBAL range_optimizer_max_mem_size=0;"

echo "Starts php aowow --sql"
php aowow --sql

echo "Starts php aowow --build"
php aowow --build=demo,gems,glyphs,enchants,itemscaling,itemsets,locales,markup,pets,profiler,realmmenu,realms,searchbox,searchplugin,statistics,talentcalc,tooltips,weightpresets

mysql -u root -proot aowow -e "UPDATE aowow_config SET value='0' WHERE \`key\`='maintenance';"
mysql -u root -proot aowow -e "UPDATE aowow_config SET value='0' WHERE \`key\`='debug';"
