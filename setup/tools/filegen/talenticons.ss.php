<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'talenticons' => [[], CLISetup::ARGV_PARAM, 'Generates icon textures for the talent calculator tool.']
    );

    protected $dbcSourceFiles = ['talenttab', 'talent', 'spell'];
    protected $setupAfter     = [['icons', 'spell'], ['simpleimg']];
    protected $requiredDirs   = ['static/images/wow/talents/icons', 'static/images/wow/hunterpettalents'];

    private const ICON_SIZE = 36;                           // px

    private $filenames = ['icons', 'warrior', 'paladin', 'hunter', 'rogue', 'priest', 'deathknight', 'shaman', 'mage', 'warlock', null, 'druid'];

    public function generate() : bool
    {
        foreach ($this->filenames as $k => $v)
        {
            if (!$v)
                continue;

            set_time_limit(10);

            for ($tree = 0; $tree < 3; $tree++)
            {
                $what    = $k ? 'classMask' : 'creatureFamilyMask';
                $set     = $k ? 1 << ($k - 1) : 1 << $tree;
                $subset  = $k ? $tree : 0;
                $path    = $k ? 'talents/icons' : 'hunterpettalents';
                $outFile = 'static/images/wow/'.$path.'/'.$v.'_'.($tree + 1).'.jpg';
                $icons   = DB::Aowow()->SelectCol(
                   'SELECT   ic.name AS iconString
                    FROM     ?_icons ic
                    JOIN     ?_spell s ON s.iconId = ic.id
                    JOIN     dbc_talent t ON t.rank1 = s.id
                    JOIN     dbc_talenttab tt ON tt.id = t.tabId
                    WHERE    tt.?# = ?d AND tt.tabNumber = ?d
                    ORDER BY t.row, t.column ASC, s.id DESC',
                    $what, $set, $subset);

                if (empty($icons))
                {
                    CLI::write('[talenticons] - query for '.$v.' tree: '.$k.' returned empty', CLI::LOG_ERROR);
                    $this->success = false;
                    continue;
                }

                $res = imageCreateTrueColor(count($icons) * self::ICON_SIZE, 2 * self::ICON_SIZE);
                if (!$res)
                {
                    $this->success = false;
                    CLI::write('[talenticons] - image resource not created', CLI::LOG_ERROR);
                    continue;
                }

                for ($i = 0; $i < count($icons); $i++)
                {
                    $imgFile = 'static/images/wow/icons/medium/'.strtolower($icons[$i]).'.jpg';
                    if (!file_exists($imgFile))
                    {
                        CLI::write('[talenticons] - raw image '.CLI::bold($imgFile). ' not found', CLI::LOG_ERROR);
                        $this->success = false;
                        break;
                    }

                    $im = imagecreatefromjpeg($imgFile);

                    // colored
                    imagecopymerge($res, $im, $i * self::ICON_SIZE, 0, 0, 0, imageSX($im), imageSY($im), 100);

                    // grayscale
                    if (imageistruecolor($im))
                        imagetruecolortopalette($im, false, 256);

                    for ($j = 0; $j < imagecolorstotal($im); $j++)
                    {
                        $color = imagecolorsforindex($im, $j);
                        $gray  = round(0.299 * $color['red'] + 0.587 * $color['green'] + 0.114 * $color['blue']);
                        imagecolorset($im, $j, $gray, $gray, $gray);
                    }
                    imagecopymerge($res, $im, $i * self::ICON_SIZE, self::ICON_SIZE, 0, 0, imageSX($im), imageSY($im), 100);
                }

                if (@imagejpeg($res, $outFile))
                    CLI::write('[talenticons] created file '.CLI::bold($outFile), CLI::LOG_OK, true, true);
                else
                {
                    $this->success = false;
                    CLI::write('[talenticons] - '.CLI::bold($outFile.'.jpg').' could not be written', CLI::LOG_ERROR);
                }
            }
        }

        return $this->success;
    }
});

?>
