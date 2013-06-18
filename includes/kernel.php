<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


ini_set('serialize_precision', 4);

require 'includes/defines.php';
require 'config/config.php';

$e = !!$AoWoWconf['debug'] ? (E_ALL & ~(E_DEPRECATED|E_USER_DEPRECATED|E_STRICT)) : 0;
error_reporting($e);

define('STATIC_URL', substr('http://'.$_SERVER['SERVER_NAME'].strtr($_SERVER['SCRIPT_NAME'], ['index.php' => '']), 0, -1));

require 'includes/Smarty-2.6.26/libs/Smarty.class.php';     // Libraray: http://www.smarty.net/
require 'includes/DbSimple/Generic.php';                    // Libraray: http://en.dklab.ru/lib/DbSimple
require 'includes/utilities.php';
require 'includes/class.user.php';
require 'includes/class.database.php';

// autoload any List-Classes
spl_autoload_register(function ($class) {
    if (strpos($class, 'List') && !strpos($class, 'Filter'))
        require 'includes/class.'.strtr($class, ['List' => '']).'.php';
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
$smarty->assign('query', [$pageCall, $pageParam]);

?>
