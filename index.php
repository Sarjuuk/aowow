<?php

define('AOWOW_REVISION', 12);

if (!file_exists('config/config.php'))
{
    $cwDir = /*$_SERVER['DOCUMENT_ROOT']; //*/getcwd();
    require 'setup/setup.php';
    exit;
}

// include all necessities, set up basics
require 'includes/kernel.php';

if (version_compare(PHP_VERSION, '5.4.0') <= 0)
{
    if (User::isInGroup(U_GROUP_EMPLOYEE))
        $smarty->internalNotice(U_GROUP_EMPLOYEE, 'PHP Version 5.4.0 or higher required! Your version is '.PHP_VERSION."\nCore functions are unavailable!");
    else
        $smarty->brb();
}

if ($AoWoWconf['maintenance'] && !User::isInGroup(U_GROUP_EMPLOYEE))
    $smarty->brb();
else if ($AoWoWconf['maintenance'] && User::isInGroup(U_GROUP_EMPLOYEE))
    $smarty->internalNotice(U_GROUP_EMPLOYEE, 'Maintenance mode enabled!');

switch ($pageCall)
{
    /* called by user */
    case 'account':                                         // account management [nyi]
    case 'achievement':
    case 'achievements':
    // case 'arena-team':
    // case 'arena-teams':
    case 'class':
    case 'classes':
    case 'currency':
    case 'currencies':
    case 'compare':                                         // tool: item comparison
    case 'event':
    case 'events':
    case 'faction':
    case 'factions':
    // case 'guild':
    // case 'guilds':
    case 'item':
    case 'items':
    case 'itemset':
    case 'itemsets':
    case 'maps':                                            // tool: map listing
    case 'npc':
    case 'npcs':
    case 'object':
    case 'objects':
    case 'pet':
    case 'pets':
    case 'profile':                                         // character profiler [nyi]
    case 'profiles':                                        // character profile listing [nyi]
    case 'quest':
    case 'quests':
    case 'race':
    case 'races':
    case 'skill':
    case 'skills':
    // case 'sound':                                        // db: sounds for zone, creature, spell, ...
    // case 'sounds':
    case 'spell':
    case 'spells':
    case 'title':
    case 'titles':
    case 'user':                                            // tool: user profiles [nyi]
    case 'zone':
    case 'zones':
        if (file_exists('pages/'.$pageCall.'.php'))
            require 'pages/'.$pageCall.'.php';
        else
            $smarty->error();
        break;
    case 'petcalc':                                         // tool: pet talent calculator
        $petCalc = true;
    case 'talent':                                          // tool: talent calculator
        require 'pages/talent.php';
        break;
    /* called by script */
    case 'cookie':                                          // lossless cookies and user settings
        if (User::$id && $pageParam && !empty($_GET[$pageParam]))
        {
            // don't panic .. again .... it get's escaped
            if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $pageParam, urldecode($_GET[$pageParam])))
                die('0');                                   // 0: success
            else
                die();
        }

        break;
    case 'contactus':
        die("not yet implemented:\n".print_r($_POST));      // 0:ok; 1:captchaInvalid; 2:tooLong; 3:noReasonGiven; 7:alreadyReported; other:prints String
    case 'comment':
        if ($pageParam == 'rating')
            die('{"success":true,"error":"","up":7,"down":9}');
        else if ($pageParam == 'rate')
            die('3');                                       // 0:success, 1:ratingban, 3:rated too often

        break;
    case 'locale':                                          // subdomain-workaround, change the language
        User::setLocale($pageParam);
        User::writeCookie();
        header('Location: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.'));
        break;
    case 'data':                                            // tool: dataset-loader
    case 'search':                                          // tool: searches
        require $pageCall.'.php';
        break;
    /* other */
    case '':                                                // no parameter given -> MainPage
        require 'pages/main.php';
        break;
    case 'latest-additions':
    case 'latest-articles':
    case 'latest-comments':
    case 'latest-screenshots':
    case 'latest-videos':
    case 'unrated-comments':
    case 'missing-screenshots':
    case 'most-comments':
    case 'random':
        require 'pages/miscTools.php';
        break;
    case 'build':
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            require 'setup/tools/dataset/'.$pageParam.'.php';
            break;
        }
    case 'sql':
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            require 'setup/tools/database/_'.$pageParam.'.php';
            break;
        }
    case 'setup':
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            require 'setup/syncronize.php';
            break;
        }
    default:                                                // unk parameter given -> ErrorPage
        if (isset($_GET['power']))
            die('$WowheadPower.register(0, '.User::$localeId.', {})');
        else                                                // in conjunction with a propper rewriteRule in .htaccess...
            $smarty->error();
        break;
}

?>
