<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/defines.php';
require 'config/config.php';
require 'includes/libs/DbSimple/Generic.php';               // Libraray: http://en.dklab.ru/lib/DbSimple (using variant: https://github.com/ivan1986/DbSimple/tree/master)
require 'includes/utilities.php';                           // misc™ data 'n func
require 'includes/ajaxHandler.class.php';                   // handles ajax and jsonp requests
require 'includes/user.class.php';
require 'includes/markup.class.php';                        // manipulate markup text
require 'includes/database.class.php';                      // wrap DBSimple
require 'includes/community.class.php';                     // handle comments, screenshots and videos
require 'includes/loot.class.php';                          // build lv-tabs containing loot-information
require 'localization/lang.class.php';
require 'pages/genericPage.class.php';


// autoload List-classes, associated filters and pages
spl_autoload_register(function ($class) {
    $class = strtolower(str_replace('Filter', '', $class));

    if (class_exists($class))                               // already registered
        return;

    if (preg_match('/[^\w]/i', $class))                     // name should contain only letters
        return;

    if (strpos($class, 'list'))
    {
        if (!class_exists('BaseType'))
            require 'includes/types/basetype.class.php';

        if (file_exists('includes/types/'.strtr($class, ['list' => '']).'.class.php'))
            require 'includes/types/'.strtr($class, ['list' => '']).'.class.php';

        return;
    }

    if (file_exists('pages/'.strtr($class, ['page' => '']).'.php'))
        require 'pages/'.strtr($class, ['page' => '']).'.php';
});


// Setup DB-Wrapper
if (!empty($AoWoWconf['aowow']['db']))
    DB::load(DB_AOWOW, $AoWoWconf['aowow']);
else
    die('no database credentials given for: aowow');

if (!empty($AoWoWconf['world']['db']))
    DB::load(DB_WORLD, $AoWoWconf['world']);

if (!empty($AoWoWconf['auth']['db']))
    DB::load(DB_AUTH, $AoWoWconf['auth']);

foreach ($AoWoWconf['characters'] as $realm => $charDBInfo)
    if (!empty($charDBInfo))
        DB::load(DB_CHARACTERS . $realm, $charDBInfo);

unset($AoWoWconf);                                          // link set up: delete passwords


// load config to constants
$sets = DB::Aowow()->select('SELECT `key` AS ARRAY_KEY, `value`, `flags` FROM ?_config');
foreach ($sets as $k => $v)
{
    // this should not have been possible
    if (!strlen($v['value']))
        continue;

    $php = $v['flags'] & CON_FLAG_PHP;

    if ($v['flags'] & CON_FLAG_TYPE_INT)
        $val = intVal($v['value']);
    else if ($v['flags'] & CON_FLAG_TYPE_FLOAT)
        $val = floatVal($v['value']);
    else if ($v['flags'] & CON_FLAG_TYPE_BOOL)
        $val = (bool)$v['value'];
    else if ($v['flags'] & CON_FLAG_TYPE_STRING)
        $val = preg_replace('/[^\p{L}0-9\s_\-\'\.,]/ui', '', $v['value']);
    else
    {
        Util::addNote(U_GROUP_ADMIN | U_GROUP_DEV, 'Kernel: '.($php ? 'PHP' : 'Aowow').' config value '.($php ? strtolower($k) : 'CFG_'.strtoupper($k)).' has no type set. Value forced to 0!');
        $val = 0;
    }

    if ($php)
        ini_set(strtolower($k), $val);
    else
        define('CFG_'.strtoupper($k), $val);
}


$secure    = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || CFG_FORCE_SSL;
$protocoll = $secure ? 'https://' : 'http://';

define('STATIC_URL', substr($protocoll.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1).'/static'); // points js to images & scripts (change here if you want to use a separate subdomain)
define('HOST_URL',   substr($protocoll.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1));           // points js to executable files

$e = CFG_DEBUG ? (E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_STRICT)) : 0;
error_reporting($e);


// debug: measure execution times
Util::execTime(CFG_DEBUG);


// Setup Session
session_set_cookie_params(15 * YEAR, '/', '', $secure, true);
session_cache_limiter('private');
session_start();
if (User::init())
    User::save();                                           // save user-variables in session

// hard-override locale for this call (should this be here..?)
// all strings attached..
if (isset($_GET['locale']) && (CFG_LOCALES & (1 << (int)$_GET['locale'])))
    User::useLocale($_GET['locale']);

Lang::load(User::$localeString);


// parse page-parameters .. sanitize before use!
@list($str, $trash) = explode('&', $_SERVER['QUERY_STRING'], 2);
@list($pageCall, $pageParam) = explode('=', $str, 2);
Util::$wowheadLink = 'http://'.Util::$subDomains[User::$localeId].'.wowhead.com/'.$str;

?>
