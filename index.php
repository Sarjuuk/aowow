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
    case 'talent':                                          // tool: talent calculator
    case 'petcalc':                                         // tool: pet talent calculator
        require 'pages/talent.php';
        break;
    /* called by script */
    case 'contactus':
        if ($pageCall == 'contactus')
        {
            // 0:ok; 1:captchaInvalid; 2:tooLong; 3:noReasonGiven; 7:alreadyReported; other:prints String
            die("not yet implemented:\n".print_r($_POST));
        }
    case 'comment':
        if ($pageParam == 'rating')
        {
            // why is this called via index...?
            die('{"success":true,"error":"","up":7,"down":9}');
        }
        else if ($pageParam == 'rate')
        {
           // 0:success, 1:ratingban, 3:rated too often
            die('3');
        }
    case 'locale':                                          // subdomain-workaround, change the language
        User::setLocale($pageParam);
        User::writeCookie();
        header('Location: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.'));
        break;
    case 'data':                                            // tool: dataset-loader
    case 'search':                                          // tool: searches
        include $pageCall.'.php';
        break;
    /* other */
    case '':                                                // no parameter given -> MainPage
        require 'pages/main.php';
        break;
    case 'latest-comments':
    case 'latest-screenshots':
    case 'latest-videos':
    case 'missing-comments':
    case 'missing-screenshots':
    case 'missing-videos':
    case 'unrated-comments':
    case 'random':
        require 'pages/miscTools.php';
        break;
    case 'build':
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            require 'setup/tools/dataset-assembler/'.$pageParam.'.php';
            break;
        }
    case 'sql':
        if (User::isInGroup(U_GROUP_EMPLOYEE))
        {
            require 'setup/tools/sql/_'.$pageParam.'.php';
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
