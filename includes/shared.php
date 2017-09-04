<?php

define('AOWOW_REVISION', 26);
define('BROWSER_CACHE_VERSION', '1e8g4h3');				// random cache version for css and js files
define('CLI', PHP_SAPI === 'cli');


$reqExt = ['SimpleXML', 'gd', 'mysqli', 'mbstring', 'fileinfo'];
$error  = '';
foreach ($reqExt as $r)
    if (!extension_loaded($r))
        $error .= 'Required Extension <b>'.$r."</b> was not found. Please check if it should exist, using \"<i>php -m</i>\"\n\n";

if (version_compare(PHP_VERSION, '5.5.0') < 0)
    $error .= 'PHP Version <b>5.5.0</b> or higher required! Your version is <b>'.PHP_VERSION."</b>.\nCore functions are unavailable!\n";

if ($error)
{
    echo CLI ? strip_tags($error) : $error;
    die();
}


// include all necessities, set up basics
require_once 'includes/kernel.php';

?>
