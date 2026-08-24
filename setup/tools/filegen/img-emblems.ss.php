<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');

/*
    ! WIP !
    the idea was to render tabards on guild and arena team pages but fizzled out due to issues with image masking in css
    https://github.com/r-o-b-o-t-o/azerothcore-armory/blob/master/static/js/emblems.js uses canvas instead, maybe another time...

    The images would need to be preped here, because the seam of the tiles is straight down the center front/back of the tabard.
    So we want the right half of the tile, double it, mirror it vertically and attach it on the right side.
    This is the (less distorted) backside of the tabard.

    Background_(\d\d)_T(U|L)_U.blp          // 1: BackgroundColor;            2: [U]pper/[L]ower image tiles
    Border_(\d\d)_(\d\d)_T(U|L)_U.blp       // 1: BorderStyle;                2: BorderColor;          3: [U]pper/[L]ower image tiles
                                                  0-5: guild; 6-9: arena team?
    Emblem_(\d\d)_(\d\d)_T(U|L)_U.blp       // 1: EmblemStyle;                2: EmblemColor;          3: [U]pper/[L]ower image tiles
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrComplexImage;

    protected array $info = array(
        'img-emblems' => [[], CLISetup::ARGV_PARAM, '[NYI] Generate emblem components for guilds and arena teams'],
    );

    public bool $isOptional = true;

    private const array SRC_FILES = array(
        'background_%d.png' => 'Background_(\d\d)_T(U|L)_U',
        'border_%d_%d.png'  => 'Border_(\d\d)_(\d\d)_T(U|L)_U',
        'emblem_%d_%d.png'  => 'Emblem_(\d\d)_(\d\d)_T(U|L)_U'
    );

    // src, resourcePath, localized, [tileOrder], [[dest, destW, destH]]
    private const array STEPS = array(
        ['textures/GuildEmblems/',   null, false, [['U'], ['L']], [['images/wow/emblems/guild', 0, 0]]],
        ['Interface/PVPFrame/',      null, false, [''],           [['images/wow/emblems/arena', 0, 0]]],
        ['Interface/PVPFrame/icons', null, false, [''],           [['images/wow/emblems/arena/icons', 0, 0]]]
    );

    public function __construct()
    {
        $this->imgPath = CLISetup::$srcDir.$this->imgPath;
        $this->maxExecTime = ini_get('max_execution_time');

        $this->genSteps = self::STEPS;

        foreach ($this->genSteps[0][self::GEN_IDX_DEST_INFO] as $dir)
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

        [, $realPath, , $tileOrder, $outInfo] = $this->genSteps[0];

        foreach (self::SRC_FILES as $destTpl => $srcTpl)
        {
            $sum     = 0;
            $files   = CLISetup::filesInPath('/'.$realPath.$srcTpl.'/i', true);
            $total   = count($files);
            $fileTpl = $outInfo[0][0].$destTpl;

            CLI::write('Processing '.$total.' files from '.$realPath.' ...');

            ini_set('max_execution_time', $this->maxExecTime);

            $sum++;
            $this->status = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);
         /*
            $file = sprintf($fileTpl, $name);

            if (!CLISetup::getOpt('force') && file_exists($file))
            {
                CLI::write($this->status.' - file '.$file.' was already processed', CLI::LOG_BLANK, true, true);
                continue;
            }

            $im = $this->assembleImage($realPath.'/'.$name, $tileOrder, count($tileOrder[0]) * 256, count($tileOrder) * 256);
            if (!$im)
            {
                CLI::write(' - could not assemble file '.$name, CLI::LOG_ERROR);
                $this->success = false;
                continue;
            }

            if (!$this->writeImageFile($im, $file, count($tileOrder[0]) * 256, count($tileOrder) * 256))
                $this->success = false;
         */
        }

        ini_set('max_execution_time', $this->maxExecTime);

        return $this->success;
    }
});

?>
