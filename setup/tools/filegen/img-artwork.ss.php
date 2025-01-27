<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    use TrComplexImage;

    protected $info = array(
        'img-artwork' => [[], CLISetup::ARGV_PARAM, 'Generate images from /glues/credits (not used on page)'],
    );

    public $isOptional = true;

    private const TILEORDER = array(
        1 => [ [1] ],
        2 => [ [1],
               [2] ],
        4 => [ [1, 2],
               [3, 4] ],
        6 => [ [1, 2, 3],
               [4, 5, 6] ],
        8 => [ [1, 2, 3, 4],
               [5, 6, 7, 8] ],
        9 => [ [1, 2, 3],
               [4, 5, 6],
               [7, 8, 9] ]
    );

    // src, resourcePath, localized, [tileOrder], [[dest, destW, destH]]
    private $genSteps = array(
        ['Glues/Credits/', null, false, self::TILEORDER, [['cache/Artworks/', 0, 0]]]
    );

    public function __construct()
    {
        $this->imgPath = CLISetup::$srcDir.$this->imgPath;
        $this->maxExecTime = ini_get('max_execution_time');

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

        $sum       = 0;
        $imgGroups = [];
        $files     = CLISetup::filesInPath('/'.str_replace('/', '\\/', $realPath).'/i', true);
        $fileTpl   = $outInfo[0][0].'%s.png';

        foreach ($files as $f)
        {
            if (preg_match('/([^\/]+)(\d).blp/i', $f, $m))
            {
                if (!$m[1] || !$m[2])
                    continue;

                if (!isset($imgGroups[$m[1]]))
                    $imgGroups[$m[1]] = $m[2];
                else if ($imgGroups[$m[1]] < $m[2])
                    $imgGroups[$m[1]] = $m[2];
            }
        }

        // errör-korrekt
        if (isset($imgGroups['Desolace']))
            $imgGroups['Desolace'] = 4;

        $total = count($imgGroups);

        CLI::write('Processing '.$total.' files from '.$realPath.' ...');

        foreach ($imgGroups as $name => $fmt)
        {
            ini_set('max_execution_time', $this->maxExecTime);

            $sum++;
            $this->status = ' - '.str_pad($sum.'/'.$total, 8).str_pad('('.number_format($sum * 100 / $total, 2).'%)', 9);
            $file = sprintf($fileTpl, $name);

            if (!CLISetup::getOpt('force') && file_exists($file))
            {
                CLI::write($this->status.' - file '.$file.' was already processed', CLI::LOG_BLANK, true, true);
                continue;
            }

            if (!isset($tileOrder[$fmt]))
            {
                CLI::write(' - pattern for file '.$name.' not set. skipping', CLI::LOG_WARN);
                $this->success = false;
                continue;
            }

            $order = $tileOrder[$fmt];

            $im = $this->assembleImage($realPath.'/'.$name, $order, count($order[0]) * 256, count($order) * 256);
            if (!$im)
            {
                CLI::write(' - could not assemble file '.$name, CLI::LOG_ERROR);
                $this->success = false;
                continue;
            }

            if (!$this->writeImageFile($im, $file, count($order[0]) * 256, count($order) * 256))
                $this->success = false;
        }

        ini_set('max_execution_time', $this->maxExecTime);

        return $this->success;
    }
});

?>
