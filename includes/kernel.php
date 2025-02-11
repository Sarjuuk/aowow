<?php

mb_internal_encoding('UTF-8');
mysqli_report(MYSQLI_REPORT_ERROR);

define('AOWOW_REVISION', 40);
define('OS_WIN', substr(PHP_OS, 0, 3) == 'WIN');            // OS_WIN as per compile info of php
define('CLI', PHP_SAPI === 'cli');
define('CLI_HAS_E', CLI &&                                  // WIN10 and later usually support ANSI escape sequences
    (!OS_WIN || (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))));


$reqExt = ['SimpleXML', 'gd', 'mysqli', 'mbstring', 'fileinfo'/*, 'gmp'*/];
$badExt = ['Intl'];                                          // Intl contains its own class Locale. What? Namespaces? Never heard of those!
$error  = '';
if ($ext = array_filter($reqExt, fn($x) => !extension_loaded($x)))
    $error .= 'Required Extension <b>'.implode(', ', $ext)."</b> was not found. Please check if it should exist, using \"<i>php -m</i>\"\n\n";

if ($ext = array_filter($badExt, fn($x) => extension_loaded($x)))
    $error .= 'Loaded Extension <b>'.implode(', ', $ext)."</b> is incompatible and must be disabled.\n\n";

if (version_compare(PHP_VERSION, '8.2.0') < 0)
    $error .= 'PHP Version <b>8.2</b> or higher required! Your version is <b>'.PHP_VERSION."</b>.\nCore functions are unavailable!\n";

if ($error)
    die(CLI ? strip_tags($error) : $error);


require_once 'includes/defines.php';
require_once 'includes/locale.class.php';
require_once 'localization/lang.class.php';
require_once 'includes/libs/DbSimple/Generic.php';          // Libraray: http://en.dklab.ru/lib/DbSimple (using variant: https://github.com/ivan1986/DbSimple/tree/master)
require_once 'includes/database.class.php';                 // wrap DBSimple
require_once 'includes/utilities.php';                      // helper functions
require_once 'includes/config.class.php';                   // Config holder
require_once 'includes/user.class.php';                     // Session handling (could be skipped for CLI context except for username and password validation used in account creation)

// todo: make everything below autoloaded
require_once 'includes/stats.class.php';                    // Game entity statistics conversion
require_once 'includes/game.php';                           // game related data & functions
require_once 'includes/profiler.class.php';                 // Profiler feature
require_once 'includes/markup.class.php';                   // manipulate markup text
require_once 'includes/community.class.php';                // handle comments, screenshots and videos
require_once 'includes/loot.class.php';                     // build lv-tabs containing loot-information
require_once 'pages/genericPage.class.php';

// TC systems
spl_autoload_register(function ($class)
{
    switch($class)
    {
        case 'SmartAI':
        case 'SmartEvent':
        case 'SmartAction':
        case 'SmartTarget':
            require_once 'includes/components/SmartAI/SmartAI.class.php';
            require_once 'includes/components/SmartAI/SmartEvent.class.php';
            require_once 'includes/components/SmartAI/SmartAction.class.php';
            require_once 'includes/components/SmartAI/SmartTarget.class.php';
            break;
        case 'Conditions':
            require_once 'includes/components/Conditions/Conditions.class.php';
            break;
    }
});

// autoload List-classes, associated filters and pages
spl_autoload_register(function ($class)
{
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

    $errName  = 'unknown error';                            // errors not in this list can not be handled by set_error_handler (as per documentation) or are ignored
    $logLevel = CLI::logLevelFromE($errNo);

    switch ($errNo)
    {
        case E_WARNING:
        case E_USER_WARNING:
            $errName  = 'WARNING';
            break;
        case E_NOTICE:
        case E_USER_NOTICE:
            $errName  = 'NOTICE';
            break;
        case E_USER_ERROR:
            $errName  = 'USER_ERROR';
        case E_USER_ERROR:
            $errName  = 'RECOVERABLE_ERROR';
        case E_STRICT:                                      // ignore STRICT and DEPRECATED
        case E_DEPRECATED:
        case E_USER_DEPRECATED:
            return true;
    }

    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $errNo, $errFile, $errLine, CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $errStr
        );

    if (CLI)
        CLI::write($errName.' - '.$errStr.' @ '.$errFile. ':'.$errLine, $logLevel);
    else if (Cfg::get('DEBUG') >= $logLevel)
        Util::addNote($errName.' - '.$errStr.' @ '.$errFile. ':'.$errLine, U_GROUP_EMPLOYEE, $logLevel);

    return true;
}, E_ALL);

// handle exceptions
set_exception_handler(function ($e)
{
    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $e->getCode(), $e->getFile(), $e->getLine(), CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $e->getMessage()
        );

    if (CLI)
        fwrite(STDERR, "\nException - ".$e->getMessage()."\n   ".$e->getFile(). '('.$e->getLine().")\n".$e->getTraceAsString()."\n\n");
    else
    {
        Util::addNote('Exception - '.$e->getMessage().' @ '.$e->getFile(). ':'.$e->getLine()."\n".$e->getTraceAsString(), U_GROUP_EMPLOYEE, CLI::LOG_ERROR);
        (new GenericPage())->error();
    }
});

// handle fatal errors
register_shutdown_function(function()
{
    if ($e = error_get_last())
    {
        if (DB::isConnected(DB_AOWOW))
            DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
                AOWOW_REVISION, $e['type'], $e['file'], $e['line'], CLI ? 'CLI' : ($_SERVER['QUERY_STRING'] ?? ''), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $e['message']
            );

        if (CLI)
            fwrite(STDERR, "\nFatal Error - ".$e['message'].' @ '.$e['file']. ':'.$e['line']."\n\n");
        else if (User::isInGroup(U_GROUP_EMPLOYEE))
            echo "\nFatal Error - ".$e['message'].' @ '.$e['file']. ':'.$e['line']."\n\n";
    }
});

// Setup DB-Wrapper
if (file_exists('config/config.php'))
    require_once 'config/config.php';
else
    $AoWoWconf = [];

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

$AoWoWconf = null;                                          // empty auths


// load config from DB
Cfg::load();


// handle non-fatal errors and notices
error_reporting(E_ALL);


if (!CLI)
{
    // not displaying the brb gnomes as static_host is missing, but eh...
    if (!DB::isConnected(DB_AOWOW) || !DB::isConnected(DB_WORLD) || !Cfg::get('HOST_URL') || !Cfg::get('STATIC_URL'))
        (new GenericPage())->maintenance();

    // Setup Session
    $cacheDir = Cfg::get('SESSION_CACHE_DIR');
    if ($cacheDir && Util::writeDir($cacheDir))
        session_save_path(getcwd().'/'.$cacheDir);

    session_set_cookie_params(15 * YEAR, '/', '', (($_SERVER['HTTPS'] ?? 'off') != 'off') || Cfg::get('FORCE_SSL'), true);
    session_cache_limiter('private');
    if (!session_start())
    {
        trigger_error('failed to start session', E_USER_ERROR);
        (new GenericPage())->error();
    }

    if (User::init())
        User::save();                                       // save user-variables in session

    // hard override locale for this call (should this be here..?)
    if (isset($_GET['locale']) && ($loc = Locale::tryFrom($_GET['locale'])))
        Lang::load($loc);
    else
        Lang::load(User::$preferedLoc);

    // set up some logging (~10 queries will execute before we init the user and load the config)
    if (Cfg::get('DEBUG') >= CLI::LOG_INFO && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
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

    // parse page-parameters .. sanitize before use!
    $str = explode('&', $_SERVER['QUERY_STRING'] ?? '', 2)[0];
    $_   = explode('=', $str, 2);
    $pageCall  = mb_strtolower($_[0]);
    $pageParam = $_[1] ?? '';
}
else if (DB::isConnected(DB_AOWOW))
    Lang::load(Locale::EN);

?>
