<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (file_exists('config/config.php'))
    require_once 'config/config.php';
else
    $AoWoWconf = [];

require_once 'includes/defines.php';
require_once 'includes/libs/DbSimple/Generic.php';          // Libraray: http://en.dklab.ru/lib/DbSimple (using variant: https://github.com/ivan1986/DbSimple/tree/master)
require_once 'includes/utilities.php';                      // misc™ data 'n func
require_once 'includes/ajaxHandler.class.php';              // handles ajax and jsonp requests
require_once 'includes/user.class.php';
require_once 'includes/markup.class.php';                   // manipulate markup text
require_once 'includes/database.class.php';                 // wrap DBSimple
require_once 'includes/community.class.php';                // handle comments, screenshots and videos
require_once 'includes/loot.class.php';                     // build lv-tabs containing loot-information
require_once 'localization/lang.class.php';
require_once 'pages/genericPage.class.php';


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
            require_once 'includes/types/basetype.class.php';

        if (file_exists('includes/types/'.strtr($class, ['list' => '']).'.class.php'))
            require_once 'includes/types/'.strtr($class, ['list' => '']).'.class.php';

        return;
    }

    if (file_exists('pages/'.strtr($class, ['page' => '']).'.php'))
        require_once 'pages/'.strtr($class, ['page' => '']).'.php';
});


// Setup DB-Wrapper
if (!empty($AoWoWconf['aowow']['db']))
    DB::load(DB_AOWOW, $AoWoWconf['aowow']);

if (!empty($AoWoWconf['world']['db']))
    DB::load(DB_WORLD, $AoWoWconf['world']);

if (!empty($AoWoWconf['auth']['db']))
    DB::load(DB_AUTH, $AoWoWconf['auth']);

if (!empty($AoWoWconf['characters']))
    foreach ($AoWoWconf['characters'] as $realm => $charDBInfo)
        if (!empty($charDBInfo))
            DB::load(DB_CHARACTERS . $realm, $charDBInfo);


// load config to constants
$sets = DB::isConnectable(DB_AOWOW) ? DB::Aowow()->select('SELECT `key` AS ARRAY_KEY, `value`, `flags` FROM ?_config') : [];
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
        $val = preg_replace('/[^\p{L}0-9~\s_\-\'\/\.,]/ui', '', $v['value']);
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


error_reporting($AoWoWconf && CFG_DEBUG ? (E_ALL & ~(E_DEPRECATED | E_USER_DEPRECATED | E_STRICT)) : 0);

$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || ($AoWoWconf && CFG_FORCE_SSL);
if (defined('CFG_STATIC_HOST'))                             // points js to images & scripts
    define('STATIC_URL', ($secure ? 'https://' : 'http://').CFG_STATIC_HOST);

if (defined('CFG_SITE_HOST'))                               // points js to executable files
    define('HOST_URL',   ($secure ? 'https://' : 'http://').CFG_SITE_HOST);


if (!CLI)
{
    // Setup Session
    session_set_cookie_params(15 * YEAR, '/', '', $secure, true);
    session_cache_limiter('private');
    session_start();
    if ($AoWoWconf && User::init())
        User::save();                                       // save user-variables in session

    // todo: (low) - move to setup web-interface (when it begins its existance)
    if (!defined('CFG_SITE_HOST') || !defined('CFG_STATIC_HOST'))
    {
        $host = substr($_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1);

        define('HOST_URL',   ($secure ? 'https://' : 'http://').$host);
        define('STATIC_URL', ($secure ? 'https://' : 'http://').$host.'/static');
        if (User::isInGroup(U_GROUP_ADMIN) && $AoWoWconf)   // initial set
        {
            DB::Aowow()->query('INSERT IGNORE INTO ?_config VALUES (?, ?, ?d, ?), (?, ?, ?d, ?)',
                'site_host',   $host,           CON_FLAG_TYPE_STRING | CON_FLAG_PERSISTENT, 'default: '.$host.' - points js to executable files (automaticly set on first run)',
                'static_host', $host.'/static', CON_FLAG_TYPE_STRING | CON_FLAG_PERSISTENT, 'default: '.$host.'/static - points js to images & scripts (automaticly set on first run)'
            );
        }
    }

    // hard-override locale for this call (should this be here..?)
    // all strings attached..
    if ($AoWoWconf)
    {
        if (isset($_GET['locale']) && (CFG_LOCALES & (1 << (int)$_GET['locale'])))
            User::useLocale($_GET['locale']);

        Lang::load(User::$localeString);
    }

    // parse page-parameters .. sanitize before use!
    @list($str, $trash) = explode('&', $_SERVER['QUERY_STRING'], 2);
    @list($pageCall, $pageParam) = explode('=', $str, 2);
    Util::$wowheadLink = 'http://'.Util::$subDomains[User::$localeId].'.wowhead.com/'.$str;
}
else if ($AoWoWconf)
    Lang::load('enus');


$AoWoWconf = null;                                          // empty auths

?>
