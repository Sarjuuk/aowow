<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // builds image-textures for the talent-calculator
    // spellIcons must be extracted and converted to at least medium size
    // this script requires the following dbc-files to be parsed and available
    // Talent, TalentTab, Spell

    function talentIcons(&$log)
    {
        $success   = true;
        $query     = 'SELECT s.iconString FROM ?_spell s JOIN dbc.talent t ON t.rank1 = s.Id JOIN dbc.talenttab tt ON tt.Id = t.tabId WHERE tt.?# = ?d AND tt.tabNumber = ?d ORDER BY t.row, t.column, t.petCategory1 ASC;';
        $dims      = 36; //v-pets
        $filenames = ['icons', 'warrior', 'paladin', 'hunter', 'rogue', 'priest', 'deathknight', 'shaman', 'mage', 'warlock', null, 'druid'];

        // create directory if missing
        if (!writeDir('static/images/wow/talents/icons', $log))
            $success = false;

        if (!writeDir('static/images/wow/hunterpettalents', $log))
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
                    $log[]   = [time(), '  error: talentIcons - query for '.$v.' tree: '.$k.' empty'];
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
                            $log[]   = [time(), '  error: talentIcons - raw image '.$imgFile. ' not found'];
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
                        $log[] = [time(), sprintf(ERR_NONE, $outFile)];
                    else
                    {
                        $success = false;
                        $log[]   = [time(), '  error: talentIcons - '.$outFile.'.jpg could not be written!'];
                    }
                }
                else
                {
                    $success = false;
                    $log[]   = [time(), '  error: talentIcons - image resource not created'];
                    continue;
                }
            }
        }

        return $success;
    }

?>
