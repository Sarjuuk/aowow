<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrComplexImage;

    protected $info = array(
        'img-talentcalc' => [[], CLISetup::ARGV_PARAM, 'Generate backgrounds for the talent calculator.'],
    );

    protected $dbcSourceFiles = ['talenttab', 'chrclasses'];

    private const DEST_DIRS = array(
        ['static/images/wow/hunterpettalents/',    0, 0],
        ['static/images/wow/talents/backgrounds/', 0, 0]
    );

    private const TILEORDER = array(
        ['-TopLeft',    '-TopRight'],
        ['-BottomLeft', '-BottomRight']
    );

    // src, resourcePath, localized, [tileOrder], [[dest, destW, destH]]
    private $genSteps = array(
        ['TalentFrame/', null, false, self::TILEORDER,  self::DEST_DIRS]
    );

    public function __construct()
    {
        $this->imgPath = CLISetup::$srcDir.$this->imgPath;
        $this->maxExecTime = ini_get('max_execution_time');

        // init directories
        foreach (self::DEST_DIRS as $dir)
            $this->requiredDirs[] = $dir[0];
    }

    public function generate() : bool
    {
        if (!$this->checkSourceDirs())
        {
            CLI::write('one or more source directories are missing:', CLI::LOG_ERROR);
            $this->success = false;
            return false;
        }

        sleep(2);

        $tTabs = DB::Aowow()->select('SELECT tt.`creatureFamilyMask`, tt.`textureFile`, tt.`tabNumber`, cc.`fileString` FROM dbc_talenttab tt LEFT JOIN dbc_chrclasses cc ON cc.`id` = IF(tt.`classMask`, LOG(2, tt.`classMask`) + 1, 0)');
        if (!$tTabs)
        {
            CLI::write(' - TalentTab.dbc or ChrClasses.dbc is empty...?', CLI::LOG_ERROR);
            $this->success = false;
            return false;
        }

        $sum   = 0;
        $total = count($tTabs);
        [, $realPath, , $tileOrder, $outInfo] = $this->genSteps[0];

        CLI::write('Processing '.$total.' files from '.$realPath.' ...');
        foreach ($tTabs as $tt)
        {
            ini_set('max_execution_time', $this->maxExecTime);
            $sum++;
            $this->status = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);

            if ($tt['creatureFamilyMask'])      // is PetCalc
            {
                $size = [244, 364];
                $outFile = sprintf($outInfo[0][0].'bg_%d.jpg', log($tt['creatureFamilyMask'], 2) + 1);
            }
            else
            {
                $size = [204, 554];
                $outFile = sprintf($outInfo[1][0].'%s_%d.jpg', strtolower($tt['fileString']), $tt['tabNumber'] + 1);
            }

            if (!CLISetup::getOpt('force') && file_exists($outFile))
            {
                CLI::write($this->status.' - file '.$outFile.' was already processed', CLI::LOG_BLANK, true, true);
                continue;
            }

            $im = $this->assembleImage($realPath.'/'.$tt['textureFile'], $tileOrder, 256 + 44, 256 + 75);
            if (!$im)
            {
                CLI::write(' - could not assemble file '.$tt['textureFile'], CLI::LOG_ERROR);
                $this->success = false;
                continue;
            }

            if (!$this->writeImageFile($im, $outFile, $size[0], $size[1]))
                $this->success = false;
        }

        ini_set('max_execution_time', $this->maxExecTime);

        return $this->success;
    }
});

?>
