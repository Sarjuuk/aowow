<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

if (!CLI)
    die('not in cli mode');


/*********************************************/
/* string setup steps together for first use */
/*********************************************/

function firstrun()
{
    require_once 'setup/tools/sqlGen.class.php';
    require_once 'setup/tools/fileGen.class.php';

    SqlGen::init(true);
    FileGen::init(true);

    /****************/
    /* define steps */
    /****************/

    $steps = array(
        // clisetup/, params, test script result,  introText,                                                                   errorText
        ['dbconfig',          null,                      'testDB',    'Please enter your database credentials.', 'could not establish connection to:'],
        ['siteconfig',        null,                      'testSelf',  'SITE_HOST and STATIC_HOST '.CLISetup::bold('must').' be set. Also enable FORCE_SSL if needed. You may also want to change other variables such as NAME, NAME_SHORT OR LOCALES.', 'could not access:'],
        // sql- and build- stuff here
        ['SqlGen::generate',  'achievementcategory',      null, null, null],
        ['SqlGen::generate',  'achievementcriteria',      null, null, null],
        ['SqlGen::generate',  'glyphproperties',          null, null, null],
        ['SqlGen::generate',  'itemenchantment',          null, null, null],
        ['SqlGen::generate',  'itemenchantmentcondition', null, null, null],
        ['SqlGen::generate',  'itemextendedcost',         null, null, null],
        ['SqlGen::generate',  'itemlimitcategory',        null, null, null],
        ['SqlGen::generate',  'itemrandomproppoints',     null, null, null],
        ['SqlGen::generate',  'lock',                     null, null, null],
        ['SqlGen::generate',  'mailtemplate',             null, null, null],
        ['SqlGen::generate',  'scalingstatdistribution',  null, null, null],
        ['SqlGen::generate',  'scalingstatvalues',        null, null, null],
        ['SqlGen::generate',  'spellfocusobject',         null, null, null],
        ['SqlGen::generate',  'spellrange',               null, null, null],
        ['SqlGen::generate',  'spellvariables',           null, null, null],
        ['SqlGen::generate',  'totemcategory',            null, null, null],
        ['SqlGen::generate',  'talents',                  null, null, null],
        ['SqlGen::generate',  'classes',                  null, null, null],
        ['SqlGen::generate',  'factions',                 null, null, null],
        ['SqlGen::generate',  'factiontemplate',          null, null, null],
        ['SqlGen::generate',  'holidays',                 null, null, null],
        ['SqlGen::generate',  'icons',                    null, null, null],
        ['SqlGen::generate',  'itemrandomenchant',        null, null, null],
        ['SqlGen::generate',  'races',                    null, null, null],
        ['SqlGen::generate',  'shapeshiftforms',          null, null, null],
        ['SqlGen::generate',  'skillline',                null, null, null],
        ['SqlGen::generate',  'emotes',                   null, null, null],
        ['SqlGen::generate',  'achievement',              null, null, null],
        ['SqlGen::generate',  'creature',                 null, null, null],
        ['SqlGen::generate',  'currencies',               null, null, null],
        ['SqlGen::generate',  'events',                   null, null, null],
        ['SqlGen::generate',  'objects',                  null, null, null],
        ['SqlGen::generate',  'pet',                      null, null, null],
        ['SqlGen::generate',  'quests',                   null, null, null],
        ['SqlGen::generate',  'quests_startend',          null, null, null],
        ['SqlGen::generate',  'spell',                    null, null, null],
        ['SqlGen::generate',  'spelldifficulty',          null, null, null],
        ['SqlGen::generate',  'taxi',                     null, null, null],
        ['SqlGen::generate',  'titles',                   null, null, null],
        ['SqlGen::generate',  'items',                    null, null, null],
        ['FileGen::generate', 'complexImg',               null, null, null],    // alphamaps generated here are requires for spawns/waypoints
        ['SqlGen::generate',  'spawns',                   null, null, null],    // this one ^_^
        ['SqlGen::generate',  'zones',                    null, null, null],
        ['SqlGen::generate',  'itemset',                  null, null, null],
        ['SqlGen::generate',  'item_stats',               null, null, null],
        ['SqlGen::generate',  'source',                   null, null, null],
        ['FileGen::generate', 'searchplugin',             null, null, null],
        ['FileGen::generate', 'power',                    null, null, null],
        ['FileGen::generate', 'searchboxScript',          null, null, null],
        ['FileGen::generate', 'demo',                     null, null, null],
        ['FileGen::generate', 'searchboxBody',            null, null, null],
        ['FileGen::generate', 'realmMenu',                null, null, null],
        ['FileGen::generate', 'locales',                  null, null, null],
        ['FileGen::generate', 'itemScaling',              null, null, null],
        ['FileGen::generate', 'realms',                   null, null, null],
        ['FileGen::generate', 'statistics',               null, null, null],
        ['FileGen::generate', 'simpleImg',                null, null, null],
        ['FileGen::generate', 'talentCalc',               null, null, null],
        ['FileGen::generate', 'pets',                     null, null, null],
        ['FileGen::generate', 'talentIcons',              null, null, null],
        ['FileGen::generate', 'glyphs',                   null, null, null],
        ['FileGen::generate', 'itemsets',                 null, null, null],
        ['FileGen::generate', 'enchants',                 null, null, null],
        ['FileGen::generate', 'gems',                     null, null, null],
        ['FileGen::generate', 'profiler',                 null, null, null],
        ['FileGen::generate', 'weightPresets',            null, null, null],
        // apply sql-updates from repository
        ['update',            null,                       null, null, null],
        ['account',           null,                       'testAcc',  'Please create your admin account.', 'There is no user with administrator priviledges in the DB.'],
        ['endSetup',          null,                       null, null, null],
    );

    /**********/
    /* helper */
    /**********/

    $saveProgress = function($nStep)
    {
        $h = fopen('cache/firstrun', 'w');
        fwrite($h, AOWOW_REVISION."\n".($nStep + 1)."\n");
        fclose($h);
    };

    function testDB(&$error)
    {
        require 'config/config.php';

        $error   = [];
        $defPort = ini_get('mysqli.default_port');

        foreach (['world', 'aowow', 'auth'] as $what)
        {
            if ($what == 'auth' && (empty($AoWoWconf['auth']) || empty($AoWoWconf['auth']['host'])))
                continue;

            $port = 0;
            if (strstr($AoWoWconf[$what]['host'], ':'))
                list($AoWoWconf[$what]['host'], $port) = explode(':', $AoWoWconf[$what]['host']);

            if ($link = @mysqli_connect($AoWoWconf[$what]['host'], $AoWoWconf[$what]['user'], $AoWoWconf[$what]['pass'], $AoWoWconf[$what]['db'], $port ?: $defPort))
                mysqli_close($link);
            else
                $error[] = ' * '.$what.': '.'['.mysqli_connect_errno().'] '.mysqli_connect_error();
        }

        return empty($error);
    }

    function testSelf(&$error)
    {
        $error = [];
        $test  = function($url, &$rCode)
        {
            $res = get_headers($url, true);

            if (preg_match("/HTTP\/[0-9\.]+\s+([0-9]+)/", $res[0], $m))
            {
                $rCode = $m[1];
                return $m[1] == 200;
            }

            $rCode = 0;
            return false;
        };

        $res  = DB::Aowow()->selectCol('SELECT `key` AS ARRAY_KEY, value FROM ?_config WHERE `key` IN ("site_host", "static_host", "force_ssl")');
        $prot = $res['force_ssl'] ? 'https://' : 'http://';
        if ($res['site_host'])
        {
            if (!$test($prot.$res['site_host'].'/README.md', $resp))
                $error[] = ' * could not access '.$prot.$res['site_host'].'/README.md ['.$resp.']';
        }
        else
            $error[] = ' * SITE_HOST is empty';

        if ($res['static_host'])
        {
            if (!$test($prot.$res['static_host'].'/css/aowow.css', $resp))
                $error[] = ' * could not access '.$prot.$res['static_host'].'/css/aowow.css ['.$resp.']';
        }
        else
            $error[] = ' * STATIC_HOST is empty';

        return empty($error);
    }

    function testAcc(&$error)
    {
        $error = [];
        return !!DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE userPerms = 1');
    }

    function endSetup()
    {
        return DB::Aowow()->query('UPDATE ?_config SET value = 0 WHERE `key` = "maintenance"');
    }

    /********************/
    /* get current step */
    /********************/

    $startStep = 0;
    if (file_exists('cache/firstrun'))
    {
        $rows = file('cache/firstrun');
        if ((int)$rows[0] == AOWOW_REVISION)
            $startStep = (int)$rows[1];
    }

    if ($startStep)
    {

        CLISetup::log('Found firstrun progression info. (Halted on subscript '.($steps[$startStep][1] ?: $steps[$startStep][0]).')', CLISetup::LOG_INFO);
        $inp = ['x' => ['continue setup? (y/n)', true, '/y|n/i']];
        $msg = '';
        if (!CLISetup::readInput($inp, true) || !$inp || strtolower($inp['x']) == 'n')
        {
            $msg = 'Starting setup from scratch...';
            $startStep = 0;
        }
        else
            $msg = 'Resuming setup from step '.$startStep.'...';

        CLISetup::log();
        CLISetup::log($msg);
        sleep(1);
    }

    /*******/
    /* run */
    /*******/

    foreach ($steps as $idx => $step)
    {
        if ($startStep > $idx)
            continue;

        if (!strpos($step[0], '::') && !is_callable($step[0]))
            require_once 'setup/tools/clisetup/'.$step[0].'.func.php';

        if ($step[3])
        {
            CLISetup::log($step[3]);
            $inp = ['x' => ['Press any key to continue', true]];

            if (!CLISetup::readInput($inp, true))                // we don't actually care about the input
                return;
        }

        while (true)
        {
            $res = call_user_func($step[0], $step[1]);

            // check script result
            if ($step[2])
            {
                if (!$step[2]($errors))
                {
                    CLISetup::log($step[4], CLISetup::LOG_ERROR);
                    foreach ($errors as $e)
                        CLISetup::log($e);
                }
                else
                {
                    $saveProgress($idx);
                    break;
                }
            }
            else if ($res !== false)
            {
                $saveProgress($idx);
                break;
            }

            $inp = ['x' => ['['.CLISetup::bold('c').']ontinue anyway? ['.CLISetup::bold('r').']etry? ['.CLISetup::bold('a').']bort?', true, '/c|r|a/i']];
            if (CLISetup::readInput($inp, true) && $inp)
            {
                CLISetup::log();
                switch(strtolower($inp['x']))
                {
                    case 'c':
                        $saveProgress($idx);
                        break 2;
                    case 'r':
                        break;
                    case 'a':
                        return;
                }
            }
            else
            {
                CLISetup::log();
                return;
            }
        }
    }

    unlink('cache/firstrun');
    CLISetup::log('setup finished', CLISetup::LOG_OK);
}

?>
