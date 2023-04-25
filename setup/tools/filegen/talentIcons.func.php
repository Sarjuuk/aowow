<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


    // builds image-textures for the talent-calculator
    // spellIcons must be extracted and converted to at least medium size
    // this script requires the following dbc-files to be available
    $reqDBC = ['talenttab', 'talent', 'spell'];

    function talentIcons()
    {
        $success   = true;
        $query     = 'SELECT ic.name AS iconString FROM ?_icons ic JOIN ?_spell s ON s.iconId = ic.id JOIN dbc_talent t ON t.rank1 = s.id JOIN dbc_talenttab tt ON tt.id = t.tabId WHERE tt.?# = ?d AND tt.tabNumber = ?d ORDER BY t.row, t.column ASC, s.id DESC';
        $dims      = 36; //v-pets
        $filenames = ['icons', 'warrior', 'paladin', 'hunter', 'rogue', 'priest', 'deathknight', 'shaman', 'mage', 'warlock', null, 'druid'];

        // create directory if missing
        if (!CLISetup::writeDir('static/images/wow/talents/icons'))
            $success = false;

        if (!CLISetup::writeDir('static/images/wow/hunterpettalents'))
            $success = false;

        foreach ($filenames as $k => $v)
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
                $icons   = DB::Aowow()->SelectCol($query, $what, $set, $subset);

                if (empty($icons))
                {
                    CLI::write('talentIcons - query for '.$v.' tree: '.$k.' returned empty', CLI::LOG_ERROR);
                    $success = false;
                    continue;
                }

                if ($res = imageCreateTrueColor(count($icons) * $dims, 2 * $dims))
                {
                    for ($i = 0; $i < count($icons); $i++)
                    {
                        $imgFile = 'static/images/wow/icons/medium/'.strtolower($icons[$i]).'.jpg';
                        if (!file_exists($imgFile))
                        {
                            CLI::write('talentIcons - raw image '.CLI::bold($imgFile). ' not found', CLI::LOG_ERROR);
                            $success = false;
                            break;
                        }

                        $im = imagecreatefromjpeg($imgFile);

                        // colored
                        imagecopymerge($res, $im, $i * $dims, 0, 0, 0, imageSX($im), imageSY($im), 100);

                        // grayscale
                        if (imageistruecolor($im))
                            imagetruecolortopalette($im, false, 256);

                        for ($j = 0; $j < imagecolorstotal($im); $j++)
                        {
                            $color = imagecolorsforindex($im, $j);
                            $gray  = round(0.299 * $color['red'] + 0.587 * $color['green'] + 0.114 * $color['blue']);
                            imagecolorset($im, $j, $gray, $gray, $gray);
                        }
                        imagecopymerge($res, $im, $i * $dims, $dims, 0, 0, imageSX($im), imageSY($im), 100);
                    }

                    if (@imagejpeg($res, $outFile))
                        CLI::write(sprintf(ERR_NONE, CLI::bold($outFile)), CLI::LOG_OK, true, true);
                    else
                    {
                        $success = false;
                        CLI::write('talentIcons - '.CLI::bold($outFile.'.jpg').' could not be written', CLI::LOG_ERROR);
                    }
                }
                else
                {
                    $success = false;
                    CLI::write('talentIcons - image resource not created', CLI::LOG_ERROR);
                    continue;
                }
            }
        }

        return $success;
    }

?>
