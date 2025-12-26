<?php

namespace Aowow;

mb_internal_encoding('UTF-8');
mb_substitute_character('none');                            // drop invalid chars entirely instead of replacing them with '?'
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR);

define('AOWOW_REVISION', 44);
define('OS_WIN', substr(PHP_OS, 0, 3) == 'WIN');            // OS_WIN as per compile info of php
define('CLI', PHP_SAPI === 'cli');
define('CLI_HAS_E', CLI &&                                  // WIN10 and later usually support ANSI escape sequences
    (!OS_WIN || (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))));


$reqExt = ['SimpleXML', 'gd', 'mysqli', 'mbstring', 'fileinfo', 'intl'/*, 'gmp'*/];
$badExt = [];
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
require_once 'localization/datetime.class.php';
require_once 'includes/libs/DbSimple/Generic.php';          // Libraray: http://en.dklab.ru/lib/DbSimple (using variant: https://github.com/ivan1986/DbSimple/tree/master)
require_once 'includes/database.class.php';                 // wrap DBSimple
require_once 'includes/utilities.php';                      // helper functions
require_once 'includes/type.class.php';                     // DB types storage and factory
require_once 'includes/cfg.class.php';                      // Config holder
require_once 'includes/user.class.php';                     // Session handling (could be skipped for CLI context except for username and password validation used in account creation)
require_once 'includes/game/misc.php';                      // Misc game related data & functions

// game client data interfaces
spl_autoload_register(function (string $class) : void
{
    if ($i = strrpos($class, '\\'))
        $class = substr($class, $i + 1);

    if (preg_match('/[^\w]/i', $class))
        return;

    if ($class == 'Stat' || $class == 'StatsContainer')     // entity statistics conversion
        require_once 'includes/game/chrstatistics.php';
    else if (file_exists('includes/game/'.strtolower($class).'.class.php'))
        require_once 'includes/game/'.strtolower($class).'.class.php';
    else if (file_exists('includes/game/loot/'.strtolower($class).'.class.php'))
        require_once 'includes/game/loot/'.strtolower($class).'.class.php';
});

// our site components
spl_autoload_register(function (string $class) : void
{
    if ($i = strrpos($class, '\\'))
        $class = substr($class, $i + 1);

    if (preg_match('/[^\w]/i', $class))
        return;

    if (file_exists('includes/components/'.strtolower($class).'.class.php'))
        require_once 'includes/components/'.strtolower($class).'.class.php';
    else if (file_exists('includes/components/frontend/'.strtolower($class).'.class.php'))
        require_once 'includes/components/frontend/'.strtolower($class).'.class.php';
    else if (file_exists('includes/components/response/'.strtolower($class).'.class.php'))
        require_once 'includes/components/response/'.strtolower($class).'.class.php';
});

// TC systems in components
spl_autoload_register(function (string $class) : void
{
    switch ($class)
    {
        case __NAMESPACE__.'\SmartAI':
        case __NAMESPACE__.'\SmartEvent':
        case __NAMESPACE__.'\SmartAction':
        case __NAMESPACE__.'\SmartTarget':
            require_once 'includes/components/SmartAI/SmartAI.class.php';
            require_once 'includes/components/SmartAI/SmartEvent.class.php';
            require_once 'includes/components/SmartAI/SmartAction.class.php';
            require_once 'includes/components/SmartAI/SmartTarget.class.php';
            break;
        case __NAMESPACE__.'\Conditions':
            require_once 'includes/components/Conditions/Conditions.class.php';
            break;
    }
});

// autoload List-classes, associated filters
spl_autoload_register(function (string $class) : void
{
    if ($i = strrpos($class, '\\'))
        $class = substr($class, $i + 1);

    if (preg_match('/[^\w]/i', $class))
        return;

    if (!stripos($class, 'list'))
        return;

    $class = strtolower(str_replace('ListFilter', 'List', $class));

    $cl = match ($class)
    {
        'localprofilelist',
        'remoteprofilelist'   => 'profile',
        'localarenateamlist',
        'remotearenateamlist' => 'arenateam',
        'localguildlist',
        'remoteguildlist'     => 'guild',
        default               => strtr($class, ['list' => ''])
    };

    if (file_exists('includes/dbtypes/'.$cl.'.class.php'))
        require_once 'includes/dbtypes/'.$cl.'.class.php';
    else
        throw new \Exception('could not register type class: '.$cl);
});

set_error_handler(function(int $errNo, string $errStr, string $errFile, int $errLine) : bool
{
    // either from test function or handled separately
    if (strstr($errStr, 'mysqli_connect') && $errNo == E_WARNING)
        return true;

    // we do not log deprecation notices
    if ($errNo & (E_DEPRECATED | E_USER_DEPRECATED))
        return true;

    $logLevel = match($errNo)
    {
        E_RECOVERABLE_ERROR, E_USER_ERROR      => LOG_LEVEL_ERROR,
        E_WARNING,           E_USER_WARNING    => LOG_LEVEL_WARN,
        E_NOTICE,            E_USER_NOTICE     => LOG_LEVEL_INFO,
        default => 0
    };
    $errName = match($errNo)
    {
        E_RECOVERABLE_ERROR       => 'RECOVERABLE_ERROR',
        E_USER_ERROR              => 'USER_ERROR',
        E_USER_WARNING, E_WARNING => 'WARNING',
        E_USER_NOTICE, E_NOTICE   => 'NOTICE',
        default                   => 'UNKNOWN_ERROR'        // errors not in this list can not be handled by set_error_handler (as per documentation) or are ignored
    };

    if (!empty($_POST['password']))
        $_POST['password'] = '******';
    if (!empty($_POST['c_password']))
        $_POST['c_password'] = '******';

    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $errNo, $errFile, $errLine, CLI ? 'CLI' : substr($_SERVER['QUERY_STRING'] ?? '', 0, 250), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $errStr
        );

    $logMsg = $errName.' - '.$errStr.' @ '.$errFile. ':'.$errLine;
    if (CLI && class_exists('CLI'))
        CLI::write($logMsg, $logLevel);
    else if (CLI)
        fwrite(STDERR, $logMsg);
    else if (Cfg::get('DEBUG') >= $logLevel)
        Util::addNote($logMsg, U_GROUP_EMPLOYEE, $logLevel);

    return true;
}, E_ALL);

// handle exceptions
set_exception_handler(function (\Throwable $e) : void
{
    if (!empty($_POST['password']))
        $_POST['password'] = '******';
    if (!empty($_POST['c_password']))
        $_POST['c_password'] = '******';

    if (DB::isConnected(DB_AOWOW))
        DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
            AOWOW_REVISION, $e->getCode(), $e->getFile(), $e->getLine(), CLI ? 'CLI' : substr($_SERVER['QUERY_STRING'] ?? '', 0, 250), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $e->getMessage()
        );

    if (CLI)
        fwrite(STDERR, "\nException - ".$e->getMessage()."\n   ".$e->getFile(). '('.$e->getLine().")\n".$e->getTraceAsString()."\n\n");
    else
    {
        Util::addNote('Exception - '.$e->getMessage().' @ '.$e->getFile(). ':'.$e->getLine()."\n".$e->getTraceAsString(), U_GROUP_EMPLOYEE, LOG_LEVEL_ERROR);
        (new TemplateResponse())->generateError();
    }
});

// handle fatal errors
register_shutdown_function(function() : void
{
    // defer undisplayed error/exception notes
    if (!CLI && ($n = Util::getNotes()))
        $_SESSION['notes'][] = [$n[0], $n[1], 'Deferred issues from previous request'];

    if ($e = error_get_last())
    {
        if (!empty($_POST['password']))
            $_POST['password'] = '******';
        if (!empty($_POST['c_password']))
            $_POST['c_password'] = '******';

        if (DB::isConnected(DB_AOWOW))
            DB::Aowow()->query('INSERT INTO ?_errors (`date`, `version`, `phpError`, `file`, `line`, `query`, `post`, `userGroups`, `message`) VALUES (UNIX_TIMESTAMP(), ?d, ?d, ?, ?d, ?, ?, ?d, ?) ON DUPLICATE KEY UPDATE `date` = UNIX_TIMESTAMP()',
                AOWOW_REVISION, $e['type'], $e['file'], $e['line'], CLI ? 'CLI' : substr($_SERVER['QUERY_STRING'] ?? '', 0, 250), empty($_POST) ? '' : http_build_query($_POST), User::$groups, $e['message']
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


// for CLI and early errors in erb context
Lang::load(Locale::EN);

// load config from DB
Cfg::load();


if (!CLI)
{
    // not displaying the brb gnomes as static_host is missing, but eh...
    if (!DB::isConnected(DB_AOWOW) || !DB::isConnected(DB_WORLD) || !Cfg::get('HOST_URL') || !Cfg::get('STATIC_URL'))
        (new TemplateResponse())->generateMaintenance();

    // Setup Session
    $cacheDir = Cfg::get('SESSION_CACHE_DIR');
    if ($cacheDir && Util::writeDir($cacheDir))
        session_save_path(getcwd().'/'.$cacheDir);

    session_set_cookie_params(15 * YEAR, '/', '', (($_SERVER['HTTPS'] ?? 'off') != 'off') || Cfg::get('FORCE_SSL'), true);
    session_cache_limiter('private');
    if (!session_start())
    {
        trigger_error('failed to start session', E_USER_ERROR);
        (new TemplateResponse())->generateError();
    }

    if (User::init())
        User::save();                                       // save user-variables in session

    // hard override locale for this call (should this be here..?)
    if (isset($_GET['locale']) && ($loc = Locale::tryFrom((int)$_GET['locale'])))
        Lang::load($loc);
    else
        Lang::load(User::$preferedLoc);

    // set up some logging (some queries will execute before we init the user and load the config)
    if (Cfg::get('DEBUG') >= LOG_LEVEL_INFO && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
    {
        DB::Aowow()->setLogger(DB::profiler(...));
        DB::World()->setLogger(DB::profiler(...));
        if (DB::isConnected(DB_AUTH))
            DB::Auth()->setLogger(DB::profiler(...));

        if (!empty($AoWoWconf['characters']))
            foreach ($AoWoWconf['characters'] as $idx => $__)
                if (DB::isConnected(DB_CHARACTERS . $idx))
                    DB::Characters($idx)->setLogger(DB::profiler(...));
    }
}

?>
