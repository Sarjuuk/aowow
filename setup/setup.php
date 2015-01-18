<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


function buildDirStruct($dir, &$idx = 1, $parent = [], $depths = 0) {
    $struct = [];

    if ($depths > 3)
        return 'null';

    $iterator = new RecursiveDirectoryIterator($dir);
    $iterator->setFlags(RecursiveDirectoryIterator::SKIP_DOTS);

    $basePath = '';
    foreach ($iterator as $path)
    {
        $path = $path->getPathname();
        if (!is_dir($path) || $path[0] == '.')              // also skip hidden dirs
            continue;

        $idx++;
        $newParent   = $parent;
        $newParent[] = basename($path);
        $struct[]    = "[".$idx.", \"".basename($path)."\", setPath.bind(this, el, '/".implode($newParent, '/')."'), ".buildDirStruct($path, $idx, $newParent, $depths + 1)."]";
    }

    return empty($struct) ? 'null' : '['.implode($struct, ",").']';
}

function checkDbcDir($dir, $reqFiles) {
    $handle = opendir($dir);
    $content = array();

    while (false !== ($entry = readdir($handle))) {
        if (is_dir($dir.'\\'.$entry))
            continue;

        $file = explode('.', $entry);
        if ($file[1] == 'dbc')
            $content[] = strToLower($file[0]);
    }


    if (empty($content))
        return array(-4, null);   // arbitrary directory .. silent death

    foreach ($reqFiles as $k => $v) {
        if (in_array(strToLower($v), $content))
            unset($reqFiles[$k]);
    }

    if (empty($reqFiles)) {
        $f = fopen($dir.'\\Resistances.dbc', 'rb');


        if (fread($f, 4) != "WDBC" || filesize($dir.'\\Resistances.dbc') < 20)
            return array(-1, 'File looks like DBC but is not in proper format!');

        $parse = dbc2array($dir.'\\Resistances.dbc', "xxxsssssssssxxxxxxxx");

        for ($i = 0; $i <= 8; $i++) {
             if (empty($parse[0][$i]))
                continue;

            if (in_array($i, array(0, 2, 3, 6, 8)))                // en, X, fr, de, X, X, es, ru
                return array($i, count($content));
            else
                return array(-2, 'locale ":$i." not supported!');
        }
    }
    $path = array_pop(explode('\\', $dir));
    return array(-3, 'Requird files are missing!', '<div>- '.str_replace($cwDir, '', $path).'\\'.implode($reqFiles, '.dbc<br />- '.str_replace($cwDir, '', $path).'\\').'.dbc</div>');
}

if (CLI || defined('TMP_BUILD'))
{
    require_once 'tools/filegen/fileGen.class.php';
    require_once 'tools/dbc.class.php';
    require_once 'tools/imagecreatefromblp.php';

    FileGen::init(@$pageParam ?: '');

    if (FileGen::$subScripts)
    {
        // start file generation
        FileGen::status('begin generation of '. implode(', ', FileGen::$subScripts));
        FileGen::status();

        // files with template
        foreach (FileGen::$tplFiles as $name => list($file, $destPath))
        {
            if (!in_array($name, FileGen::$subScripts))
                continue;

            if (!file_exists(FileGen::$tplPath.$file.'.in'))
            {
                FileGen::status(sprintf(ERR_MISSING_FILE, FileGen::$tplPath.$file.'.in'), MSG_LVL_ERROR);
                continue;
            }

            if (!FileGen::writeDir($destPath))
                continue;

            if ($content = file_get_contents(FileGen::$tplPath.$file.'.in'))
            {
                if ($dest = @fOpen($destPath.$file, "w"))
                {
                    // replace constants
                    $content = strtr($content, FileGen::$txtConstants);

                    // must generate content
                    // PH format: /*setup:<setupFunc>*/
                    if (preg_match('/\/\*setup:([\w\d_-]+)\*\//i', $content, $m))
                    {
                        if (file_exists('setup/tools/filegen/'.$m[1].'.func.php'))
                            require_once 'setup/tools/filegen/'.$m[1].'.func.php';
                        else
                        {
                            FileGen::status(sprintf(ERR_MISSING_INCL, $m[1], 'setup/tools/filegen/'.$m[1].'.func.php'), MSG_LVL_ERROR);
                            continue;
                        }

                        if (function_exists($m[1]))
                            $content = str_replace('/*setup:'.$m[1].'*/', $m[1](), $content);
                        else
                        {
                            $content = '';
                            FileGen::status('Placeholder in template file does not match any known function name.', MSG_LVL_ERROR);
                        }
                    }

                    if (fWrite($dest, $content))
                        FileGen::status(sprintf(ERR_NONE, $destPath.$file), MSG_LVL_OK);
                    else
                        FileGen::status(sprintf(ERR_WRITE_FILE, $destPath.$file, MSG_LVL_ERROR));

                    fClose($dest);
                }
                else
                    FileGen::status(sprintf(ERR_CREATE_FILE, $destPath.$file), MSG_LVL_ERROR);
            }
            else
                FileGen::status(sprintf(ERR_READ_FILE, FileGen::$tplPath.$file.'.in'), MSG_LVL_ERROR);
        }

        // files without template
        foreach (FileGen::$datasets as $file)
        {
            if (!in_array($file, FileGen::$subScripts))
                continue;

            if (file_exists('setup/tools/filegen/'.$file.'.func.php'))
            {
                require_once 'setup/tools/filegen/'.$file.'.func.php';

                if (function_exists($file))
                    FileGen::status(' - subscript \''.$file.'\' returned '.($file() ? 'sucessfully' : 'with errors'));
                else
                    FileGen::status(' - subscript \''.$file.'\' not defined in included file', MSG_LVL_ERROR);

                set_time_limit(FileGen::$defaultExecTime);      // reset to default for the next script
            }
            else
                FileGen::status(sprintf(ERR_MISSING_INCL, $file, 'setup/tools/filegen/'.$file.'.func.php', MSG_LVL_ERROR));
        }


        // end
        FileGen::status();
        FileGen::status('finished file generation');
    }
    else
        FileGen::status('no valid script names supplied');
}
/*
else
{
    if (isset($_GET['pathMenu'])) {
        // set_time_limit(240);    // parsing directory-structures seems to be costy...
        die(buildDirStruct($cwDir.'/setup', $c));
    }

    $step = @intVal($_GET['step']);
    $fields = @explode(';', $_GET['fields']);

    if ($step == 1) {
        // unset saved credentials
        $_SESSION['step1']['progress'] &= ~(1 << $fields[0]);
        $_SESSION['step1'][$fields[0]]  = array($fields[1], $fields[3], $fields[4], $fields[2]);

        // try to connect to db with data provided
        $link = @mysql_connect($fields[1], $fields[3], $fields[4], true);
        if ($link) {
            switch ($fields[0]) {
                case 0:
                    if (mysql_select_db($fields[2], $link)) {
                        if (mysql_fetch_row(mysql_query("SHOW TABLES FROM ".$fields[2]." LIKE '".$fields[5]."glyphpropperties'")))
                            die('{"errno":-1, "errstr":"Tables already present in this database will be overwritten!"}');
                        else {
                            $_SESSION['step1']['progress'] |= 0x1;
                            die('{"errno":0, "errstr":""}');
                        }
                    }
                    else if (mysql_errno() == 1044)     // why doesn't this occur earlier?
                        die('{"errno":'.mysql_errno().', "errstr":"'.mysql_error().'"}');
                    else {
                        $_SESSION['step1']['progress'] |= 0x1;
                        die('{"errno":-1, "errstr":"Database will be created during installation!"}');
                    }
                case 1:
                    if (mysql_select_db($fields[2], $link)) {
                        if (mysql_fetch_row(mysql_query("SHOW TABLES FROM ".$fields[2]." LIKE '".$fields[5]."item_template'"))) {
                            $_SESSION['step1']['progress'] |= 0x2;
                            die('{"errno":0, "errstr":""}');
                        }
                    }
                    break;
                case 2:
                    if (mysql_select_db($fields[2], $link)) {
                        if (mysql_fetch_row(mysql_query("SHOW TABLES FROM ".$fields[2]." LIKE '".$fields[5]."account'"))) {
                            $_SESSION['step1']['progress'] |= 0x4;
                            die('{"errno":0, "errstr":""}');
                        }
                    }
                    break;
                case 3:
                    if (mysql_select_db($fields[2], $link)) {
                        if (mysql_fetch_row(mysql_query("SHOW TABLES FROM ".$fields[2]." LIKE '".$fields[5]."characters'"))) {
                            $_SESSION['step1']['progress'] |= 0x8;
                            die('{"errno":0, "errstr":""}');
                        }
                    }
                    break;
            }
            if(!mysql_errno())
               die('{"errno":-1, "errstr":"Required table not found in selected database!"}');
            else
               die('{"errno":'.mysql_errno().', "errstr":"'.mysql_error().'"}');
        }
        else
            die('{"errno":'.mysql_errno().', "errstr":"'.mysql_error().'"}');

    }
    else if ($step == 2) {
        $final = array();

        // sanitize .. clamp dir-choice to DOCUMENT_ROOT
        $dir = $cwDir.'/setup'.str_replace(['/..', '/.', '../', './'], '', str_replace(',', '/', $fields[1]));

        // unset saved credentials
        $_SESSION['step2']['progress'] &= ~(1 << $fields[0]);
        $_SESSION['step2'][$fields[0]] = $dir;

        if (!is_dir($dir))
            die(json_encode(array(str_replace($cwDir, '', $dir) => array("errno" => 5, "errstr" => "Not a directory!"))));

        $handle = opendir($dir);

        switch ($fields[0]) {
            case 0: {
                $reqDBCs = array_keys($dbcStructure);
                // check this directory
                $result = checkDbcDir($dir, $reqDBCs);
                if ($result[0] < 0 && isset($result[1]))
                    $final[str_replace($cwDir, '', $dir)] = array('errno' => -$result[0], 'errstr' => $result[1], 'tooltip' => $result[2]);
                else if ($result[0] >= 0) {
                    if ($result[1] == 246)
                        $final[str_replace($cwDir, '', $dir)] = array('locale' => $result[0], 'errno' => 0, 'errstr' => '');
                    else
                        $final[str_replace($cwDir, '', $dir)] = array('locale' => $result[0], 'errno' => -1, 'errstr' => (246 - $result[1]).' optional files missing.');
                }

                // check first-level child direcotries
                while (false !== ($entry = readdir($handle))) {
                    if ($entry == "." || $entry == "..")
                        continue;

                    if (is_dir($dir.'\\'.$entry) === true) {
                        $result = checkDbcDir($dir.'\\'.$entry, $reqDBCs);

                        if ($result[0] < 0 && isset($result[1]))
                            $final[$entry] = array('errno' => -$result[0], 'errstr' => $result[1], 'tooltip' => $result[2]);
                        else if ($result[0] >= 0) {
                            if ($result[1] == 246)
                                $final[$entry] = array('locale' => $result[0], 'errno' => 0, 'errstr' => '');
                            else
                                $final[$entry] = array('locale' => $result[0], 'errno' => -1, 'errstr' => (246 - $result[1]).' optional files missing.');
                        }
                    }
                }
                foreach ($final as $v)
                    if ($v['errno'] <= 0)
                        $_SESSION['step2']['progress'] |= 0x1;

                die(json_encode($final));
            }
            case 1: {
                $loc = array('enUS' => 0, 'enGB' => 0, 'frFR' => 2, 'deDE' => 3, 'esES' => 6, 'esMX' => 6, 'ruRU' => 8);
                $expectDir = array('Icons' => array(6308, "/[\w\d\_]+[\.tga|\s|\.]?.blp/i"), 'Spellbook' => array(20, "/UI\-Glyph\-Rune\-[0-9]+\.blp/i"), 'Worldmap' => array(117));

                foreach ($loc as $k => $v) {
                    if (!is_dir($dir.'\\'.$k))
                        continue;

                    if (isset($final[$v]))
                        continue;

                    $final[$v] = array();
                    $j = 0;
                    foreach ($expectDir as $sk => $sv) {
                        if (!is_dir($dir.'\\'.$k.'\\'.$sk))
                            break;

                        $handle = opendir($dir.'\\'.$k.'\\'.$sk);
                        $i = 0;
                        while (false !== ($entry = readdir($handle))) {
                            if (isset($sv[1])) {
                                if (is_dir($dir.'\\'.$k.'\\'.$sk.'\\'.$entry) === true)
                                    continue;

                                if (preg_match($sv[1], $entry, $result))
                                    $i++;
                            }
                            else {
                                if (is_dir($dir.'\\'.$k.'\\'.$sk.'\\'.$entry) === false || $entry == '.' || $entry == '..')
                                    continue;

                                $i++;
                            }
                        }
                        if ($i == $sv[0]) {
                            $final[$v][$sk] = array('errno' => 0, 'errstr' => '');
                            $final['total'] |= (1 << $j);
                        }
                        else
                            $final[$v][$sk] = array('errno' => 1, 'errstr' => ($sv[0] - $i).' files missing in '.$k.'\\'.$sk.'!');

                        $j++;
                    }
                    if (empty($final[$v]))
                        $final[$v] = array('errno' => 3, 'errstr' => 'locale directory '.$k.' is empty!');
                }
                if ($final['total'] == 0x7)
                    $_SESSION['step2']['progress'] |= 0x2;

                die(json_encode($final));
            }
            case 2: {
                while (false !== ($entry = readdir($handle))) {
                    if (is_dir($entry) === true)
                        continue;

                    $file = explode('.', $entry);
                    if ($file[1] != 'sql')
                        continue;

                    $fRes = fopen($dir."\\".$entry, 'rb');
                    if (preg_match('/\/\* AoWoW 3\.3\.5 (en|fr|de|es|ru)[a-z]{2} locales \*\//i', fread($fRes, 30), $result))
                        $final[$result[1]] = str_replace($cwDir, '', $dir).'\\'.$entry;
                }
                if (!empty($final))
                    $_SESSION['step2']['progress'] |= 0x4;

                die(json_encode($final));
            }
        }
    }

    include 'setup.tpl.php';
}
*/


?>
