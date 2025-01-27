<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


trait TrDBCcopy
{
    public function __construct()
    {
        $this->info = array(
            $this->command => [[], CLISetup::ARGV_PARAM, 'COPY: ' . $this->dbcSourceFiles[0] . '.dbc -> aowow_'.$this->command],
        );
    }

    public function generate() : bool
    {
        if (!$this->dbcSourceFiles)
        {
            CLI::write('[sql] SetupScript '.$this->command.' is set up for DBCcopy but has no source set!', CLI::LOG_ERROR);
            return false;
        }
        else if (count($this->dbcSourceFiles) != 1)
            CLI::write('[sql] SetupScript '.$this->command.' is set up for DBCcopy but has multiple sources set!', CLI::LOG_WARN);

        CLI::write('[sql] copying '.$this->dbcSourceFiles[0].'.dbc into aowow_'.$this->command);

        $dbc = new DBC($this->dbcSourceFiles[0], ['temporary' => false, 'tableName' => 'aowow_'.$this->command]);
        if ($dbc->error)
            return false;

        return !!$dbc->readFile();
    }
}

trait TrCustomData
{
    // apply post generator custom data
    public function applyCustomData() : bool
    {
        $ok = true;
        foreach ((DB::Aowow()->selectCol('SELECT `entry` AS ARRAY_KEY, `field` AS ARRAY_KEY2, `value` FROM ?_setup_custom_data WHERE `command` = ?', $this->getName()) ?: []) as $id => $data)
        {
            try
            {
                DB::Aowow()->query('UPDATE ?_'.$this->getName().' SET ?a WHERE id = ?d', $data, $id);
            }
            catch (Exception $e)
            {
                trigger_error('custom data for entry #'.$id.': '.$e->getMessage(), E_USER_ERROR);
                $ok = false;
            }
        }

        return $ok;
    }
}

trait TrTemplateFile
{
    public function generate() : bool
    {
        $this->templateFill();
        return $this->success;
    }

    private function &templateCopy() : iterable
    {
        if (!$this->fileTemplateSrc || count($this->fileTemplateSrc) != count($this->fileTemplateDest))
        {
            CLI::write('[build] template file definitions missing or malformed', CLI::LOG_ERROR);
            $this->success = false;
            return;
        }

        foreach ($this->fileTemplateSrc as $idx => $srcFile)
        {
            $file = $this->fileTemplatePath.$srcFile;

            if (!file_exists($file))
            {
                CLI::write('[build] template file is missing - '.CLI::bold($file), CLI::LOG_ERROR);
                $this->success = false;
                return;
            }

            $content = file_get_contents($file);
            if (!$content)
            {
                CLI::write('[build] template file is not readable - '.CLI::bold($file), CLI::LOG_ERROR);
                $this->success = false;
                return;
            }

            // replace constants
            $content = Cfg::applyToString($content);

            yield $content;

            if (CLISetup::writeFile($this->fileTemplateDest[$idx], $content))
                continue;

            $this->success = false;
            return;
        }
    }

    private function templateFill() : void
    {
        foreach ($this->templateCopy() as &$content)
        {
            if (!$this->success)                            //templateCopy() fucked up somehow?
                return;

            // PH format: /*setup:<setupFunc>*/
            if (preg_match_all('/\/\*setup:([\w\-_]+)\*\//i', $content, $m))
            {
                foreach ($m[1] as $func)
                {
                    if (method_exists($this, $func))
                        $content = str_replace('/*setup:'.$func.'*/', $this->$func(), $content);
                    else
                    {
                        CLI::write('['.$this->getName().'] No function for was registered for placeholder '.$func.'().', CLI::LOG_ERROR);
                        $this->success = false;
                        return;
                    }
                }
            }
        }
    }
}

trait TrImageProcessor
{
    private $imgPath     = '%sInterface/';
    private $status      = '';
    private $maxExecTime = 30;

    private const GEN_IDX_SRC_PATH  = 0;
    private const GEN_IDX_SRC_REAL  = 1;
    private const GEN_IDX_LOCALE    = 2;
    private const GEN_IDX_SRC_INFO  = 3;
    private const GEN_IDX_DEST_INFO = 4;

    private const JPEG_QUALITY = 85;                        // 0: worst - 100: best

    private function checkSourceDirs() : bool
    {
        $outTblLen  = 0;
        $foundCache = [];

        foreach ($this->genSteps as $i => [$subDir, $realPaths, $localized, , ])
        {
            if ($realPaths)
                continue;

            // multiple genSteps can require the same resource
            if (isset($foundCache[$subDir]))
            {
                $this->genSteps[$i][self::GEN_IDX_SRC_REAL] = $foundCache[$subDir];
                continue;
            }

            $outTblLen = max($outTblLen, strlen($subDir));

            $path = $this->imgPath.$subDir;
            if ($p = CLISetup::filesInPathLocalized($path, $this->success, $localized))
            {
                $foundCache[$subDir] = $p;
                $this->genSteps[$i][self::GEN_IDX_SRC_REAL] = $p;
            }
            else
                $this->success = false;
        }

        $locList = [];
        foreach (Locale::cases() as $loc)
        {
            if (!$loc->validate() || !in_array($loc, CLISetup::$locales))
                continue;

            $locList = array_merge($locList, $loc->gameDirs());
        }

        CLI::write('[img-proc] required resources overview:', CLI::LOG_INFO);

        $foundCache = [];
        foreach ($this->genSteps as [$subDir, $realPaths, $localized, , ])
        {
            // one line per unique resource
            if (isset($foundCache[$subDir]))
                continue;

            $foundCache[$subDir] = true;
            if (!$realPaths)
            {
                CLI::write(CLI::red('MISSING').' - '.str_pad($subDir, $outTblLen).' @ '.sprintf($this->imgPath, '['.implode('/ ', $locList).'/]').$subDir);
                $this->success = false;
            }
            else if ($localized)
            {
                $foundLoc = [];
                foreach (CLISetup::$locales as $lId => $loc)
                    foreach ($loc->gameDirs() as $xp)
                        if (isset($realPaths[$lId]) && ($n = stripos($realPaths[$lId], DIRECTORY_SEPARATOR.$xp.DIRECTORY_SEPARATOR)))
                            $foundLoc[$lId] = substr($realPaths[$lId], $n + 1, 4);

                if ($diff = array_diff_key(CLISetup::$locales, $foundLoc))
                {
                    $buff = [];
                    foreach ($diff as $loc)
                        $buff[] = CLI::red($loc->json());
                    foreach ($foundLoc as $str)
                        $buff[] = CLI::green($str);

                    CLI::write(CLI::yellow('PARTIAL').' - '.str_pad($subDir, $outTblLen).' @ '.sprintf($this->imgPath, '['.implode('/ ', $buff).'/]').$subDir);
                }
                else
                    CLI::write(CLI::green('FOUND  ').' - '.str_pad($subDir, $outTblLen).' @ '.sprintf($this->imgPath, '['.implode('/ ', $foundLoc).'/]').$subDir);
            }
            else
                CLI::write(CLI::green('FOUND  ').' - '.str_pad($subDir, $outTblLen).' @ '.reset($realPaths));
        }

        CLI::write();

        // if not localized directly return result
        foreach ($this->genSteps as $i => [$subDir, $realPaths, $localized, , ])
            if (!$localized && $realPaths)
                $this->genSteps[$i][self::GEN_IDX_SRC_REAL] = reset($realPaths);

        return $this->success;
    }

    // prefer manually converted PNG files (as the imagecreatefromblp-script has issues with some formats)
    // alpha channel issues observed with locale deDE Hilsbrad and Elwynn - maps
    // see: https://github.com/Kanma/BLPConverter
    private function loadImageFile(string $path, ?bool &$noSrc = false) : ?GdImage
    {
        $result = null;
        $noSrc  = false;
        $path   = preg_replace('/\.(png|blp)$/i', '', $path);

        $file = $path.'.png';
        if (CLISetup::fileExists($file))
        {
            CLI::write('[img-proc] manually converted png file present for '.$file, CLI::LOG_INFO);
            $result = imagecreatefrompng($file);
        }

        if (!$result)
        {
            $file = $path.'.blp';
            if (CLISetup::fileExists($file))
                $result = imagecreatefromblp($file);
            else
                $noSrc = true;
        }

        return $result;
    }

    private function writeImageFile(GdImage $src, string $outFile, array $srcDims, array $destDims) : bool
    {
        $success = false;
        $outRes  = imagecreatetruecolor($destDims['w'], $destDims['h']);
        $ext     = substr($outFile, -3, 3);

        imagesavealpha($outRes, true);
        if ($ext == 'png')
        {
            imagealphablending($outRes, false);
            $transparentindex = imagecolorallocatealpha($outRes, 255, 255, 255, 127);
            imagefill($outRes, 0, 0, $transparentindex);
        }

        imagecopyresampled($outRes, $src, $destDims['x'], $destDims['x'], $srcDims['x'], $srcDims['y'], $destDims['w'], $destDims['h'], $srcDims['w'], $srcDims['h']);

        switch ($ext)
        {
            case 'jpg':
                $success = imagejpeg($outRes, $outFile, self::JPEG_QUALITY);
                break;
            case 'gif':
                $success = imagegif($outRes, $outFile);
                break;
            case 'png':
                $success = imagepng($outRes, $outFile);
                break;
            default:
                CLI::write('[img-proc] '.$this->status.' - unsupported file fromat: '.$ext, CLI::LOG_WARN);
        }

        imagedestroy($outRes);

        if ($success)
        {
            chmod($outFile, Util::FILE_ACCESS);
            CLI::write('[img-proc] '.$this->status.' - image '.$outFile.' written', CLI::LOG_OK, true, true);
        }
        else
            CLI::write('[img-proc] '.$this->status.' - could not create image '.$outFile, CLI::LOG_ERROR);

        return $success;
    }
}

trait TrComplexImage
{
    use TrImageProcessor { TrImageProcessor::writeImageFile as _writeImageFile; }

    private function writeImageFile(GdImage $src, string $outFile, int $w, int $h) : bool
    {
        $srcDims = array(
            'x' => 0,
            'y' => 0,
            'w' => imagesx($src),
            'h' => imagesy($src)
        );
        $destDims = array(
            'x' => 0,
            'y' => 0,
            'w' => $w,
            'h' => $h
        );

        return $this->_writeImageFile($src, $outFile, $srcDims, $destDims);
    }

    private function createAlphaImage(int $w, int $h) : ?GdImage
    {
        $img = imagecreatetruecolor($w, $h);
        if (!$img)
            return null;

        imagesavealpha($img, true);
        imagealphablending($img, false);

        $bgColor = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefilledrectangle($img, 0, 0, imagesx($img) - 1, imagesy($img) - 1, $bgColor);

        imagecolortransparent($img, $bgColor);
        imagealphablending($img, true);

        imagecolordeallocate($img, $bgColor);

        return $img;
    }

    private function assembleImage(string $baseName, array $tileData, int $destW, int $destH) : ?GdImage
    {
        $dest = imagecreatetruecolor($destW, $destH);
        if (!$dest)
            return null;

        imagesavealpha($dest, true);
        imagealphablending($dest, false);

        $tileH = $destH;
        foreach ($tileData as $y => $row)
        {
            $tileW = $destW;
            foreach ($row as $x => $suffix)
            {
                $src = $this->loadImageFile($baseName.$suffix, $noSrcFile);
                if (!$src)
                {
                    if ($noSrcFile)
                        CLI::write('[img-proc-c] tile '.$baseName.$suffix.'.blp missing.', CLI::LOG_ERROR);

                    unset($dest);
                    return null;
                }

                imagecopyresampled($dest, $src, 256 * $x, 256 * $y, 0, 0, min($tileW, 256), min($tileH, 256), min($tileW, 256), min($tileH, 256));
                $tileW -= 256;

                unset($src);
            }
            $tileH -= 256;
        }

        return $dest;
    }
}

abstract class SetupScript
{
    // FileGen
    protected $requiredDirs       = [];
    protected $fileTemplateDest   = [];
    protected $fileTemplatePath   = 'setup/tools/filegen/templates/';
    protected $fileTemplateSrc    = [];

    // SQLGen
    protected $result = '';

    // FileGen + SQLGen
    protected $dbcSourceFiles     = [];                     // relies on these dbc files. Read into db if related table is missing
    protected $worldDependency    = [];                     // query when this table changed (--sync command)

    protected $info               = [];                     // arr: 0 => self, n => genSteps        cmd => [[arr<str>:optionalArgs], int:argFlags, str:description]
    protected $setupAfter         = [[], []];               // [[sqlgen], [filegen]]                used to sort scripts that rely on each other being executed in the right order (script names are not nessecarily the same as their table names)

    protected $success            = true;
    protected $localized          = false;                  // push locale directories onto $requiredDirs?
    protected $useGlobalStrings   = false;                  // uses data from interface/framexml/globalstrings.lua

    public $isOptional            = false;                  // not a part of the setup chain


    abstract public function generate() : bool;

    public function getRequiredDBCs() : array
    {
        return $this->dbcSourceFiles;
    }

    public function getSelfDependencies() : array
    {
        return $this->setupAfter;
    }

    public function getRemoteDependencies() : array
    {
        return $this->worldDependency;
    }

    public function getName() : string
    {
        reset($this->info);
        return key($this->info);
    }

    public function getInfo() // : string|int
    {
        // info: name => [param, paramFlags, description]
        return (reset($this->info)[2] ?? '').($this->isOptional ? ' - '.Cli::yellow('[omitted by setup]') : '');
    }

    public function getSubCommands() : array
    {
        $sub = [];

        if (count($this->info) > 1)
            $sub = array_slice($this->info, 1, null, true);

        return $sub;
    }

    public function getRequiredDirs(): array
    {
        return $this->requiredDirs;
    }

    public function fulfillRequirements() : bool
    {
        // create directory structure
        $newDirs = 0;
        $existed = false;
        foreach ($this->getRequiredDirs() as $dir)
        {
            $dirs = [];
            if (!$this->localized)
                $dirs[] = $dir;
            else
                foreach (CLISetup::$locales as $loc)
                    $dirs[] = $dir . $loc->json() . DIRECTORY_SEPARATOR;

            foreach ($dirs as $d)
            {
                if (!CLISetup::writeDir($d, $existed))
                {
                    CLI::write('[build] could not create directory: '.CLI::bold($d), CLI::LOG_ERROR);
                    return false;
                }
            }

            $newDirs += ($existed ? 0 : 1);
        }

        if ($newDirs)
            CLI::write('[build] created '.$newDirs.' extra paths');

        // load DBC files
        if (!in_array('TrDBCcopy', class_uses($this)))
        {
            foreach ($this->getRequiredDBCs() as $req)
            {
                if (CLISetup::loadDBC($req))
                    continue;

                CLI::write('[sql/build] '. $this->getName() . ' is missing dbc file ' . $req . '. Skipping...', CLI::LOG_ERROR);
                return false;
            }
        }

        if ($this->useGlobalStrings)
            if (!CLISetup::loadGlobalStrings())
                return false;

        return true;
    }

    public function writeCLIHelp() : bool
    {
        if (count($this->info) < 2)
            return false;                                   // help not provided, display parents help text

        $lines = [];
        foreach ($this->info as $cmd => [$shortOpts, $flags, $text])
        {
            $line = ($flags & CLISetup::ARGV_PARAM ? '' : '--').$cmd;
            foreach ($shortOpts as $so)
                $line .= ' | '.(strlen($so) == 1 ? '-'.$so : '--'.$so);

            $lines[] = [$line, $text];
        }

        CLI::writeTable($lines);

        if ($this->dbcSourceFiles)
        {
            sort($this->dbcSourceFiles);
            CLI::write('Will use client data tables:', CLI::LOG_NONE, false);
            foreach ($this->dbcSourceFiles as $dbc)
                CLI::write(' * '.$dbc.'.dbc', CLI::LOG_NONE, false);
            CLI::write();
        }

        if ($this->worldDependency)
        {
            sort($this->worldDependency);
            CLI::write('Depends on world db tables:', CLI::LOG_NONE, false);
            foreach ($this->worldDependency as $tbl)
                CLI::write(' * '.$tbl, CLI::LOG_NONE, false);
            CLI::write();
        }

        if ($this->setupAfter[0])
        {
            sort($this->setupAfter[0]);
            CLI::write('Requires data generators:', CLI::LOG_NONE, false);
            foreach ($this->setupAfter[0] as $sql)
                CLI::write(' * '.$sql, CLI::LOG_NONE, false);
            CLI::write();
        }

        if ($this->setupAfter[1])
        {
            sort($this->setupAfter[1]);
            CLI::write('Requires file generators:', CLI::LOG_NONE, false);
            foreach ($this->setupAfter[1] as $build)
                CLI::write(' * '.$build, CLI::LOG_NONE, false);
            CLI::write();
        }

        CLI::write();
        return true;                                        // help was provided, skip help from parent
    }

    protected function reapplyCCFlags(string $tbl, int $type) : void
    {
        // reapply flags for community content as these are lost when the table is rebuild

        if (preg_match('/\W/i', $tbl))
        {
            trigger_error('[sql] reapplyCCFlags() - invalid table name');
            return;
        }

        DB::Aowow()->query('UPDATE ?_'.$tbl.' x, ?_comments    y SET x.`cuFlags` = x.`cuFlags` | ?d WHERE x.`id` = y.`typeId` AND y.`type` = ?d AND y.`flags`  & ?d', CUSTOM_HAS_COMMENT,    $type, CC_FLAG_APPROVED);
        DB::Aowow()->query('UPDATE ?_'.$tbl.' x, ?_screenshots y SET x.`cuFlags` = x.`cuFlags` | ?d WHERE x.`id` = y.`typeId` AND y.`type` = ?d AND y.`status` & ?d', CUSTOM_HAS_SCREENSHOT, $type, CC_FLAG_APPROVED);
        DB::Aowow()->query('UPDATE ?_'.$tbl.' x, ?_videos      y SET x.`cuFlags` = x.`cuFlags` | ?d WHERE x.`id` = y.`typeId` AND y.`type` = ?d AND y.`status` & ?d', CUSTOM_HAS_VIDEO,      $type, CC_FLAG_APPROVED);
    }
}

?>
