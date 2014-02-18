<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


ini_set('serialize_precision', 4);

require 'includes/defines.php';
require 'config/config.php';

$e = !!$AoWoWconf['debug'] ? (E_ALL & ~(E_DEPRECATED|E_USER_DEPRECATED|E_STRICT)) : 0;
error_reporting($e);

define('STATIC_URL',        substr('http://'.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1));

define('SQL_LIMIT_NONE',       0);
define('SQL_LIMIT_SEARCH',     $AoWoWconf['searchLimit']);
define('SQL_LIMIT_QUCKSEARCH', $AoWoWconf['quickSearchLimit']);
define('SQL_LIMIT_DEFAULT',    $AoWoWconf['defaultLimit']);

require 'includes/libs/Smarty-2.6.26/libs/Smarty.class.php';// Libraray: http://www.smarty.net/
// require 'includes/libs/Smarty-3.1.14/libs/Smarty.class.php';     // Libraray: http://www.smarty.net/
require 'includes/libs/DbSimple/Generic.php';               // Libraray: http://en.dklab.ru/lib/DbSimple (using mysqli variant: https://bitbucket.org/brainreaver/dbsimple/src)
require 'includes/utilities.php';
require 'localization/lang.class.php';
require 'includes/user.class.php';
require 'includes/database.class.php';

// autoload List-Classes and Associated Filters
spl_autoload_register(function ($class) {
    if (strpos($class, 'List') && !class_exists($class))
    {
        if (!class_exists('BaseType'))
            require 'includes/types/basetype.class.php';

        require 'includes/types/'.strtr($class, ['List' => '']).'.class.php';
    }
});

// debug: measure execution times
Util::execTime(!!$AoWoWconf['debug']);

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
        DB::load(DB_CHARACTERS + $realm, $charDBInfo);

// create Template-Object
$smarty = new SmartyAoWoW($AoWoWconf);

// attach template to util (yes bandaid, shut up and code me a fix)
Util::$pageTemplate = &$smarty;

// Setup Session
if (isset($_COOKIE[COOKIE_AUTH]))
{
    $offset = intval($_COOKIE[COOKIE_AUTH][1]);

    if ($id = hexdec(substr($_COOKIE[COOKIE_AUTH], 2, $offset)))
    {
        User::init($id);

        switch (User::Auth())
        {
            case AUTH_OK:
            case AUTH_BANNED:
                User::writeCookie();
                break;
            default:
                User::destroy();
        }
    }
    else
        User::init(0);
}
else
    User::init(0);

User::setLocale();

// assign lang/locale, userdata, characters and custom profiles
User::assignUserToTemplate($smarty, true);

// parse page-parameters .. sanitize before use!
@list($str, $trash) = explode('&', $_SERVER['QUERY_STRING'], 2);
@list($pageCall, $pageParam) = explode('=', $str, 2);
$smarty->assign('wowhead', 'http://'.Util::$subDomains[User::$localeId].'.wowhead.com/'.$str);

?>
