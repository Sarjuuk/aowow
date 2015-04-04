<?php

define('AOWOW_REVISION', 12);
define('CLI', PHP_SAPI === 'cli');


$reqExt = ['SimpleXML', 'gd', 'mysqli', 'mbstring'];
$error  = '';
foreach ($reqExt as $r)
    if (!extension_loaded($r))
        $error .= 'Required Extension <b>'.$r."</b> was not found. Please check if it should exist, using \"<i>php -m</i>\"\n\n";

if (version_compare(PHP_VERSION, '5.5.0') < 0)
    $error .= 'PHP Version <b>5.5.0</b> or higher required! Your version is <b>'.PHP_VERSION."</b>.\nCore functions are unavailable!\n";

// not in root dir
if (CLI && getcwd().DIRECTORY_SEPARATOR.'index.php' != __FILE__)
    $error .= "Aowow must be used from root directory\n";

if ($error)
{
    echo CLI ? strip_tags($error) : $error;
    die();
}


// include all necessities, set up basics
require_once 'includes/kernel.php';


if (CLI)
{
    require 'setup/setup.php';
    die();
}


// maybe add additional setup checks?
if (!DB::isConnectable(DB_AOWOW) || !DB::isConnectable(DB_WORLD))
    (new GenericPage($pageCall))->maintenance();


$altClass = '';
switch ($pageCall)
{
    /* called by user */
    case '':                                                // no parameter given -> MainPage
        $altClass = 'home';
    case 'home':
    case 'admin':
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
    case 'petcalc':                                         // tool: pet talent calculator
        if ($pageCall == 'petcalc')
            $altClass = 'talent';
    case 'profile':                                         // character profiler [nyi]
    case 'profiles':                                        // character profile listing [nyi]
    case 'profiler':                                        // character profiler main page
    case 'quest':
    case 'quests':
    case 'race':
    case 'races':
    case 'screenshot':                                      // prepare uploaded screenshots
    case 'search':                                          // tool: searches
    case 'skill':
    case 'skills':
    // case 'sound':                                        // db: sounds for zone, creature, spell, ...
    // case 'sounds':
    case 'spell':
    case 'spells':
    case 'talent':                                          // tool: talent calculator
    case 'title':
    case 'titles':
    // case 'user':                                         // tool: user profiles [nyi]
    case 'video':
    case 'zone':
    case 'zones':
        if (in_array($pageCall, ['admin', 'account', 'profile']))
        {
            if (($_ = (new AjaxHandler($pageParam))->handle($pageCall)) !== null)
            {
                header('Content-type: application/x-javascript; charset=utf-8');
                die((string)$_);
            }
        }

        $_ = ($altClass ?: $pageCall).'Page';
        (new $_($pageCall, $pageParam))->display();
        break;
    /* other pages */
    case 'whats-new':
    case 'searchplugins':
    case 'searchbox':
    case 'tooltips':
    case 'help':
    case 'faq':
    case 'aboutus':
        (new MorePage($pageCall, $pageParam))->display();
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
        (new UtilityPage($pageCall, $pageParam))->display();
        break;
    /* called by script */
    case 'data':                                            // tool: dataset-loader
    case 'cookie':                                          // lossless cookies and user settings
    case 'contactus':
    case 'comment':
    case 'go-to-comment':                                   // find page the comment is on and forward
    case 'locale':                                          // subdomain-workaround, change the language
        if (($_ = (new AjaxHandler($pageParam))->handle($pageCall)) !== null)
        {
            header('Content-type: application/x-javascript; charset=utf-8');
            die((string)$_);
        }
        break;
    default:                                                // unk parameter given -> ErrorPage
        if (isset($_GET['power']))
            die('$WowheadPower.register(0, '.User::$localeId.', {})');
        else                                                // in conjunction with a proper rewriteRule in .htaccess...
            (new GenericPage($pageCall))->error();
        break;
}

?>
