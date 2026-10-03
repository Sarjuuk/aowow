<?php

namespace Aowow;

define('AOWOW_REVISION', 68);
require_once __DIR__.'/components/errorlog.class.php';
ErrorLog::configurePhp();                                   // enforce native-output settings in PHP-FPM too

mb_internal_encoding('UTF-8');
mb_substitute_character('none');                            // drop invalid chars entirely instead of replacing them with '?'
error_reporting(E_ALL);
mysqli_report(MYSQLI_REPORT_ERROR);

define('OS_WIN', substr(PHP_OS, 0, 3) == 'WIN');            // OS_WIN as per compile info of php
define('CLI', PHP_SAPI === 'cli');
define('CLI_HAS_E', CLI &&                                  // WIN10 and later usually support ANSI escape sequences
    (!OS_WIN || (function_exists('sapi_windows_vt100_support') && sapi_windows_vt100_support(STDOUT))));


$reqExt = ['curl', 'SimpleXML', 'gd', 'mysqli', 'mbstring', 'fileinfo', 'intl'/*, 'gmp'*/];
$badExt = [];
$error  = '';
if ($ext = array_filter($reqExt, fn($x) => !extension_loaded($x)))
    $error .= 'Required Extension <b>'.implode(', ', $ext).'</b> was not found. Please check if it should exist, using "<pre>php -m</pre>"';

if ($ext = array_filter($badExt, fn($x) => extension_loaded($x)))
    $error .= 'Loaded Extension <b>'.implode(', ', $ext).'</b> is incompatible and must be disabled.';

if (version_compare(PHP_VERSION, '8.4.0') < 0)
    $error .= 'PHP Version <b>8.4</b> or higher required! Your version is <b>'.PHP_VERSION.'</b>.'.PHP_EOL.'Core functions are unavailable!';

if ($error)
{
    if (CLI)
    {
        fwrite(STDERR, strip_tags($error).PHP_EOL.PHP_EOL);
        exit(1);
    }
    die($error);
}


require_once 'includes/defines.php';
require_once 'includes/locale.class.php';
require_once 'localization/lang.class.php';
require_once 'localization/datetime.class.php';
require_once 'includes/libs/autoload.php';                  // Composer libraries
require_once 'includes/database.php';                       // wrap dg/dibi (https://https://dibi.nette.org/)
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

ErrorLog::install();

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

// Deployment-owned cache keys and operator allowlists must remain outside DB/cache configuration.
if (file_exists('config/security.php'))
    require_once 'config/security.php';


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

    session_set_cookie_params([
        'lifetime' => 15 * YEAR,
        'path' => '/',
        'domain' => '',
        'secure' => (($_SERVER['HTTPS'] ?? 'off') != 'off') || Cfg::get('FORCE_SSL'),
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    session_cache_limiter('private');
    if (!session_start())
    {
        trigger_error('failed to start session', E_USER_WARNING);
        (new TemplateResponse())->generateError();
    }

    Csrf::assertRequest();                                  // reject unsafe requests before session/account writes

    if (User::init())
        User::save();                                       // save user-variables in session

    // hard override locale for this call (should this be here..?)
    if (isset($_GET['locale']) && ($loc = Locale::tryFrom((int)$_GET['locale'])))
        Lang::load($loc);
    else
        Lang::load(User::$preferedLoc);
}

?>
