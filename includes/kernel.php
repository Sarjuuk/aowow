<?php

mb_internal_encoding('UTF-8');
mysqli_report(MYSQLI_REPORT_ERROR);

define('AOWOW_REVISION', 36);
define('OS_WIN', substr(PHP_OS, 0, 3) == 'WIN');            // OS_WIN as per compile info of php
define('CLI', PHP_SAPI === 'cli');
define('CLI_HAS_E', CLI &&                                  // WIN10 and later usually support ANSI escape sequences
    (!OS_WIN || (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))));


$reqExt = ['SimpleXML', 'gd', 'mysqli', 'mbstring', 'fileinfo'/*, 'gmp'*/];
$error  = '';
if ($ext = array_filter($reqExt, function($x) { return !extension_loaded($x); }))
    $error .= 'Required Extension <b>'.implode(', ', $ext)."</b> was not found. Please check if it should exist, using \"<i>php -m</i>\"\n\n";

if (version_compare(PHP_VERSION, '8.0.0') < 0)
    $error .= 'PHP Version <b>8.0</b> or higher required! Your version is <b>'.PHP_VERSION."</b>.\nCore functions are unavailable!\n";

if ($error)
    die(CLI ? strip_tags($error) : $error);


if (file_exists('config/config.php'))
    require_once 'config/config.php';
else
    $AoWoWconf = [];


require_once 'includes/defines.php';
require_once 'includes/libs/DbSimple/Generic.php';          // Libraray: http://en.dklab.ru/lib/DbSimple (using variant: https://github.com/ivan1986/DbSimple/tree/master)
require_once 'includes/utilities.php';                      // helper functions
require_once 'includes/game.php';                           // game related data & functions
require_once 'includes/profiler.class.php';
require_once 'includes/user.class.php';
require_once 'includes/markup.class.php';                   // manipulate markup text
require_once 'includes/database.class.php';                 // wrap DBSimple
require_once 'includes/community.class.php';                // handle comments, screenshots and videos
require_once 'includes/loot.class.php';                     // build lv-tabs containing loot-information
require_once 'includes/smartAI.class.php';
require_once 'localization/lang.class.php';
require_once 'pages/genericPage.class.php';


// autoload List-classes, associated filters and pages
spl_autoload_register(function ($class) {
    $class = strtolower(str_replace('ListFilter', 'List', $class));

    if (class_exists($class))                               // already registered
        return;

    if (preg_match('/[^\w]/i', $class))                     // name should contain only letters
        return;

    if (stripos($class, 'list'))
    {
        require_once 'includes/basetype.class.php';

        $cl = strtr($class, ['list' => '']);
        if ($cl == 'remoteprofile' || $cl == 'localprofile')
            $cl = 'profile';
        if ($cl == 'remotearenateam' || $cl == 'localarenateam')
            $cl = 'arenateam';
        if ($cl == 'remoteguild' || $cl == 'localguild')
            $cl = 'guild';

        if (file_exists('includes/types/'.$cl.'.class.php'))
            require_once 'includes/types/'.$cl.'.class.php';
        else
            throw new Exception('could not register type class: '.$cl);

        return;
    }
    else if (stripos($class, 'ajax') === 0)
    {
        require_once 'includes/ajaxHandler.class.php';      // handles ajax and jsonp requests

        if (file_exists('includes/ajaxHandler/'.strtr($class, ['ajax' => '']).'.class.php'))
            require_once 'includes/ajaxHandler/'.strtr($class, ['ajax' => '']).'.class.php';
        else
            throw new Exception('could not register ajaxHandler class: '.$class);

        return;
    }
    else if (file_exists('pages/'.strtr($class, ['page' => '']).'.php'))
        require_once 'pages/'.strtr($class, ['page' => '']).'.php';
});

set_error_handler(function($errNo, $errStr, $errFile, $errLine)
{
    // either from test function or handled separately
    if (strstr($errStr, 'mysqli_connect') && $errNo == E_WARNING)
        return true;

    $errName = 'unknown error';                             // errors not in this list can not be handled by set_error_handler (as per documentation) or are ignored
    $uGroup  = U_GROUP_EMPLOYEE;

    if ($errNo == E_WARNING)                                // 0x0002
        $errName = 'E_WARNING';
    else if ($errNo == E_PARSE)                             // 0x0004
        $errName = 'E_PARSE';
    else if ($errNo == E_NOTICE)                            // 0x0008
        $errName = 'E_NOTICE';
    else if ($errNo == E_USER_ERROR)                        // 0x0100
        $errName = 'E_USER_ERROR';
    else if ($errNo == E_USER_WARNING)                      // 0x0200
        $errName = 'E_USER_WARNING';
    else if ($errNo == E_USER_NOTICE)                       // 0x0400
    {
        $errName = 'E_USER_NOTICE';
        $uGroup  = U_GROUP_STAFF;
    }
    else if ($errNo == E_RECOVERABLE_ERROR)                 // 0x1000
        $errName = 'E_RECOVERABLE_ERROR';

    Util::addNote($uGroup, $errName.' - '.$errStr.' @ '.$errFile. ':'.$errLine);
    if (CLI)
        CLI::write($errName.' - '.$errStr.' @ '.$errFile. ':'.$errLine, $errNo & (E_WARNING | E_USER_WARNING | E_NOTICE | E_USER_NOTICE) ? CLI::LOG_WARN : CLI::LOG_ERROR);

    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $errNo, $errFile, $errLine, CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), User::$groups, $errStr
        );

    return true;
}, E_AOWOW);

// handle exceptions
set_exception_handler(function ($e)
{
    Util::addNote(U_GROUP_EMPLOYEE, 'Exception - '.$e->getMessage().' @ '.$e->getFile(). ':'.$e->getLine()."\n".$e->getTraceAsString());

    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $e->getCode(), $e->getFile(), $e->getLine(), CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), User::$groups, $e->getMessage()
        );

    if (!CLI)
        (new GenericPage())->error();
    else
        echo "\nException - ".$e->getMessage()."\n   ".$e->getFile(). '('.$e->getLine().")\n".$e->getTraceAsString()."\n\n";
});

// handle fatal errors
register_shutdown_function(function()
{
    if (($e = error_get_last()) && $e['type'] & (E_ERROR | E_COMPILE_ERROR | E_CORE_ERROR))
    {
        if (DB::isConnected(DB_AOWOW))
            DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
                AOWOW_REVISION, $e['type'], $e['file'], $e['line'], CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), User::$groups, $e['message']
            );

        if (CLI)
            echo "\nFatal Error - ".$e['message'].' @ '.$e['file']. ':'.$e['line']."\n\n";

        // cant generate a page for web view :(
        die();
    }
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
function loadConfig(bool $noPHP = false) : void
{
    $sets = DB::isConnected(DB_AOWOW) ? DB::Aowow()->select('SELECT `key` AS ARRAY_KEY, `value`, `flags` FROM ?_config') : [];
    foreach ($sets as $k => $v)
    {
        $php = $v['flags'] & CON_FLAG_PHP;
        if ($php && $noPHP)
            continue;

        // this should not have been possible
        if (!strlen($v['value']) && !($v['flags'] & CON_FLAG_TYPE_STRING) && !$php)
        {
            trigger_error('Aowow config value CFG_'.strtoupper($k).' is empty - config will not be used!', E_USER_ERROR);
            continue;
        }

        if ($v['flags'] & CON_FLAG_TYPE_INT)
            $val = intVal($v['value']);
        else if ($v['flags'] & CON_FLAG_TYPE_FLOAT)
            $val = floatVal($v['value']);
        else if ($v['flags'] & CON_FLAG_TYPE_BOOL)
            $val = (bool)$v['value'];
        else if ($v['flags'] & CON_FLAG_TYPE_STRING)
            $val = preg_replace("/[\p{C}]/ui", '', $v['value']);
        else if ($php)
        {
            trigger_error('PHP config value '.strtolower($k).' has no type set - config will not be used!', E_USER_ERROR);
            continue;
        }
        else // if (!$php)
        {
            trigger_error('Aowow config value CFG_'.strtoupper($k).' has no type set - value forced to 0!', E_USER_ERROR);
            $val = 0;
        }

        if ($php)
            ini_set(strtolower($k), $val);
        else if (!defined('CFG_'.strtoupper($k)))
            define('CFG_'.strtoupper($k), $val);
    }

    $required = ['CFG_SCREENSHOT_MIN_SIZE', 'CFG_CONTACT_EMAIL', 'CFG_NAME', 'CFG_NAME_SHORT', 'CFG_FORCE_SSL', 'CFG_DEBUG'];
    foreach ($required as $r)
        if (!defined($r))
            define($r, '');
}
loadConfig();


$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || (!empty($AoWoWconf['aowow']) && CFG_FORCE_SSL);
if (defined('CFG_STATIC_HOST'))                             // points js to images & scripts
    define('STATIC_URL', ($secure ? 'https://' : 'http://').CFG_STATIC_HOST);

if (defined('CFG_SITE_HOST'))                               // points js to executable files
    define('HOST_URL',   ($secure ? 'https://' : 'http://').CFG_SITE_HOST);


// handle non-fatal errors and notices
error_reporting(CFG_DEBUG ? E_AOWOW : 0);


if (!CLI)
{
    // not displaying the brb gnomes as static_host is missing, but eh...
    if (!DB::isConnected(DB_AOWOW) || !DB::isConnected(DB_WORLD) || !defined('HOST_URL') || !defined('STATIC_URL'))
        (new GenericPage())->maintenance();

    // Setup Session
    if (CFG_SESSION_CACHE_DIR && Util::writeDir(CFG_SESSION_CACHE_DIR))
        session_save_path(getcwd().'/'.CFG_SESSION_CACHE_DIR);

    session_set_cookie_params(15 * YEAR, '/', '', $secure, true);
    session_cache_limiter('private');
    if (!session_start())
    {
        trigger_error('failed to start session', E_USER_ERROR);
        (new GenericPage())->error();
    }

    if (User::init())
        User::save();                                       // save user-variables in session

    // set up some logging (~10 queries will execute before we init the user and load the config)
    if (CFG_DEBUG && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
    {
        DB::Aowow()->setLogger(['DB', 'profiler']);
        DB::World()->setLogger(['DB', 'profiler']);
        if (DB::isConnected(DB_AUTH))
            DB::Auth()->setLogger(['DB', 'profiler']);

        if (!empty($AoWoWconf['characters']))
            foreach ($AoWoWconf['characters'] as $idx => $__)
                if (DB::isConnected(DB_CHARACTERS . $idx))
                    DB::Characters($idx)->setLogger(['DB', 'profiler']);
    }

    // hard-override locale for this call (should this be here..?)
    // all strings attached..
    if (isset($_GET['locale']))
    {
        $loc = intVal($_GET['locale']);
        if ($loc <= MAX_LOCALES && $loc >= 0 && (CFG_LOCALES & (1 << $loc)))
            User::useLocale($loc);
    }

    Lang::load(User::$localeId);

    // parse page-parameters .. sanitize before use!
    $str = explode('&', mb_strtolower($_SERVER['QUERY_STRING'] ?? ''), 2)[0];
    $_   = explode('=', $str, 2);
    $pageCall  = $_[0];
    $pageParam = $_[1] ?? '';
}
else if (DB::isConnected(DB_AOWOW))
    Lang::load(LOCALE_EN);

$AoWoWconf = null;                                          // empty auths

?>
