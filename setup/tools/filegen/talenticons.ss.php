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

    public function generate() : bool
    {
        /***************/
        /* Hunter Pets */
        /***************/

        for ($tabIdx = 0; $tabIdx < 3; $tabIdx++)
        {
            $outFile = 'static/images/wow/hunterpettalents/icons_'.($tabIdx + 1).'.jpg';

            if ($tex = $this->compileTexture('creatureFamilyMask', 1 << $tabIdx, 0))
            {
                if (!imagejpeg($tex, $outFile))
                {
                    CLI::write('[talenticons] - '.CLI::bold($outFile.'.jpg').' could not be written', CLI::LOG_ERROR);
                    $this->success = false;
                }
                else
                    CLI::write('[talenticons] created file '.CLI::bold($outFile), CLI::LOG_OK, true, true);
            }
            else
                $this->success = false;
        }


        /***********/
        /* Players */
        /***********/

        foreach (ChrClass::cases() as $class)
        {
            set_time_limit(10);

            for ($tabIdx = 0; $tabIdx < 3; $tabIdx++)
            {
                $outFile = 'static/images/wow/talents/icons/'.$class->json().'_'.($tabIdx + 1).'.jpg';

                if ($tex = $this->compileTexture('classMask', $class->toMask(), $tabIdx))
                {
                    if (!imagejpeg($tex, $outFile))
                    {
                        CLI::write('[talenticons] - '.CLI::bold($outFile.'.jpg').' could not be written', CLI::LOG_ERROR);
                        $this->success = false;
                    }
                    else
                        CLI::write('[talenticons] created file '.CLI::bold($outFile), CLI::LOG_OK, true, true);
                }
                else
                    $this->success = false;
            }
        }

        return $this->success;
    }

    private function compileTexture(string $ttField, int $searchMask, int $tabIdx) : ?GDImage
    {
        $icons = DB::Aowow()->SelectCol(
           'SELECT   ic.`name` AS "iconString"
            FROM     ?_icons ic
            JOIN     ?_spell s ON s.`iconId` = ic.`id`
            JOIN     dbc_talent t ON t.`rank1` = s.`id`
            JOIN     dbc_talenttab tt ON tt.`id` = t.`tabId`
            WHERE    tt.?# = ?d AND tt.`tabNumber` = ?d
            ORDER BY t.`row`, t.`column` ASC, s.`id` DESC',
            $ttField, $searchMask, $tabIdx);

        if (empty($icons))
        {
            CLI::write('[talenticons] - query for '.$ttField.': '.$searchMask.' on idx: '.$tabIdx.' returned empty', CLI::LOG_ERROR);
            return null;
        }

        $res = imageCreateTrueColor(count($icons) * self::ICON_SIZE, 2 * self::ICON_SIZE);
        if (!$res)
        {
            CLI::write('[talenticons] - image resource not created', CLI::LOG_ERROR);
            return null;
        }

        for ($i = 0; $i < count($icons); $i++)
        {
            $imgFile = 'static/images/wow/icons/medium/'.strtolower($icons[$i]).'.jpg';
            if (!file_exists($imgFile))
            {
                CLI::write('[talenticons] - raw image '.CLI::bold($imgFile). ' not found', CLI::LOG_ERROR);
                return null;
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

        return $res;
    }
});

?>
