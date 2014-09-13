<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// shared funcs
function writeFile($file, $content, &$log)
{
    $success = false;
    if ($handle = @fOpen($file, "w"))
    {
        if (fWrite($handle, $content))
        {
            $success = true;
            $log[] = [time(), sprintf(ERR_NONE, $file)];
        }
        else
            $log[] = [time(), sprintf(ERR_WRITE_FILE, $file)];

        fClose($handle);
    }
    else
        $log[] = [time(), sprintf(ERR_CREATE_FILE, $file)];

    if ($success)
        chmod($file, 0766);

    return $success;
}

function writeDir($dir, &$log)
{
    if (is_dir($dir) || @mkdir($dir, 0766, true))
        return true;

    $log[] = [time(), '  error: could not create output directory '.$dir];
    return false;
}

// shared strings
define('ERR_CREATE_FILE',  '  error: could not create file at destination %s');
define('ERR_WRITE_FILE',   '  error: could not write to file at destination %s');
define('ERR_READ_FILE',    '  error: file %s could not be read');
define('ERR_MISSING_FILE', '  error: file %s not found');
define('ERR_NONE',         'success: created file(s) %s');
define('ERR_MISSING_INCL', '  error: required function %s() could not be found at %s');

$log       = [];
$locales   = [];
$tplPath   = 'setup/tools/filegen/templates/';
$pairs     = array(
    'CFG_NAME'       => CFG_NAME,
    'CFG_NAME_SHORT' => CFG_NAME_SHORT,
    'HOST_URL'       => HOST_URL,
    'STATIC_URL'     => STATIC_URL
);
$tplFiles  = array(
    'searchplugin'    => ['aowow.xml',      'static/download/searchplugins/'],
    'power'           => ['power.js',       'static/widgets/'               ],
    'searchboxScript' => ['searchbox.js',   'static/widgets/'               ],
    'demo'            => ['demo.html',      'static/widgets/power/'         ],
    'searchboxBody'   => ['searchbox.html', 'static/widgets/searchbox/'     ],
    'realmMenu'       => ['profile_all.js', 'static/js/'                    ],
    'locales'         => ['locale.js',      'static/js/'                    ],
//  'itemScaling      => ['item-scaling',   'datasets/'                     ],  # provided 'as is', as dbc-content doesn't usualy change
);
$nullFiles = array(
    'realms',       'statistics',       'profiler',         // profiler related
    'talents',      'talentIcons',      'glyphs',           // talentCalc related
    'itemsets',     'enchants',         'gems',             // comparison related
    'pets',
);


// restrict actual locales
foreach (Util::$localeStrings as $idx => $str)
    if ($str && (CFG_LOCALES & (1 << $idx)))
        $locales[] = $idx;


// check $pageParam; limit to real scriptNames
$scList = array_merge(array_keys($tplFiles), $nullFiles);
if ($pageParam)
    $scList = array_intersect(explode(';', $pageParam), $scList);


if ($scList)
{
    // start
    $log[] = [time(), 'begin generation of '. implode(', ', $scList)];
    $log[] = null;

    // files with template
    foreach ($tplFiles as $name => list($file, $destPath))
    {
        if ($scList && !in_array($name, $scList))
            continue;

        if (!file_exists($tplPath.$file.'.in'))
        {
            $log[] = [time(), sprintf(ERR_MISSING_FILE, $tplPath.$file.'.in')];
            continue;
        }

        if (!writeDir($destPath, $log))
            continue;

        if ($content = file_get_contents($tplPath.$file.'.in'))
        {
            if ($dest = @fOpen($destPath.$file, "w"))
            {
                // replace constants
                $content = strtr($content, $pairs);

                // must generate content
                // PH format: /*setup:<setupFunc>*/
                if (preg_match('/\/\*setup:([\w\d_-]+)\*\//i', $content, $m))
                {
                    $res = '';
                    if (file_exists('setup/tools/filegen/'.$m[1].'.func.php'))
                    {
                        include 'setup/tools/filegen/'.$m[1].'.func.php';
                        $res = $m[1]($log, $locales);
                    }
                    else
                        $log[] = [time(), sprintf(ERR_MISSING_INCL, $m[1], 'setup/tools/filegen/'.$m[1].'.func.php')];

                    $content = str_replace('/*setup:'.$m[1].'*/', $res, $content);
                }

                if (fWrite($dest, $content))
                {
                    fClose($dest);
                    $log[] = [time(), sprintf(ERR_NONE, $destPath.$file)];
                }
                else
                {
                    $log[] = [time(), sprintf(ERR_WRITE_FILE, $destPath.$file)];
                    fClose($dest);
                    continue;
                }
            }
            else
            {
                $log[] = [time(), sprintf(ERR_CREATE_FILE, $destPath.$file)];
                continue;
            }
        }
        else
        {
            $log[] = [time(), sprintf(ERR_READ_FILE, $tplPath.$file.'.in')];
            continue;
        }
    }

    // files without template
    foreach ($nullFiles as $file)
    {
        if ($scList && !in_array($file, $scList))
            continue;

        if (file_exists('setup/tools/filegen/'.$file.'.func.php'))
        {
            include 'setup/tools/filegen/'.$file.'.func.php';
            if ($file($log, $locales))
                $log[] = [time(), ' - subscript returned sucessfully'];
            else
                $log[] = [time(), ' - subscript returned with errors'];

            set_time_limit(30);                             // reset to default for the next script
        }
        else
            $log[] = [time(), sprintf(ERR_MISSING_INCL, $file, 'setup/tools/filegen/'.$file.'.func.php')];
    }

    // end
    $log[] = null;
    $log[] = [time(), 'finished file generation'];
}
else
    $log[] = [time(), 'no valid script names supplied'];


// print accumulated log
echo "<pre>\n";
foreach ($log as $l)
    if ($l)
        echo date('H:i:s', $l[0]) . ' ' . $l[1]."\n";
    else
        echo "\n";
echo "</pre>\n";


?>