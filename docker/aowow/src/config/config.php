<?php
if (!defined('AOWOW_REVISION'))
    die('illegal access');

$AoWoWconf['aowow'] = array (
    'host' => getenv('DB_AOWOW_HOST') ?? 'localhost:3306',
    'user' => getenv('DB_AOWOW_USER') ?? 'root',
    'pass' => getenv('DB_AOWOW_PASS') ?? 'root',
    'db' =>  getenv('DB_AOWOW_DB') ??'aowow',
    'prefix' => getenv('DB_AOWOW_PREFIX') ?? 'aowow_',
);
$AoWoWconf['world'] = array (
    'host' => getenv('DB_WORLD_HOST') ?? 'localhost:3306',
    'user' => getenv('DB_WORLD_USER') ?? 'root',
    'pass' => getenv('DB_WORLD_PASS') ?? 'root',
    'db' => getenv('DB_WORLD_DB') ?? 'trinitycore_world',
    'prefix' => getenv('DB_WORLD_PREFIX') ?? '',
);
$AoWoWconf['auth'] = array (
    'host' => getenv('DB_AUTH_HOST') ?? 'localhost:3306',
    'user' => getenv('DB_AUTH_USER') ?? 'root',
    'pass' => getenv('DB_AUTH_PASS') ?? 'root',
    'db' => getenv('DB_AUTH_DB') ?? 'trinitycore_auth',
    'prefix' => getenv('DB_AUTH_PREFIX') ?? '',
);
$AoWoWconf['characters']['1'] = array (
    'host' => getenv('DB_CHARACTERS_1_HOST') ?? 'localhost:3306',
    'user' => getenv('DB_CHARACTERS_1_USER') ?? 'root',
    'pass' => getenv('DB_CHARACTERS_1_PASS') ?? 'root',
    'db' =>  getenv('DB_CHARACTERS_1_DB') ?? 'trinitycore_characters',
    'prefix' => getenv('DB_CHARACTERS_1_PREFIX') ?? '',
);
