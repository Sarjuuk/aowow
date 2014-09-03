<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // builds image-textures for the talent-calculator
    // spellIcons must be extracted and converted to at least medium size
    // this script requires the following dbc-files to be parsed and available
    // Talent, TalentTab, Spell

    $query     = 'SELECT s.iconString FROM ?_spell s JOIN dbc.talent t ON t.rank1 = s.Id JOIN dbc.talenttab tt ON tt.Id = t.tabId WHERE tt.?# = ?d AND tt.tabNumber = ?d ORDER BY t.row, t.column, t.petCategory1 ASC;';
    $dims      = 36; //v-pets
    $filenames = ['icons', 'warrior', 'paladin', 'hunter', 'rogue', 'priest', 'deathknight', 'shaman', 'mage', 'warlock', null, 'druid'];

    // create directory if missing
    if (!is_dir('static/images/wow/talents/icons'))
        mkdir('static/images/wow/talents/icons', 0755, true);

    if (!is_dir('static/images/wow/hunterpettalents'))
        mkdir('static/images/wow/hunterpettalents', 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($filenames as $k => $v)
    {
        if (!$v)
            continue;

        set_time_limit(10);

        for ($tree = 0; $tree < 3; $tree++)
        {
            $what   = $k ? 'classMask' : 'creatureFamilyMask';
            $set    = $k ? 1 << ($k - 1) : 1 << $tree;
            $subset = $k ? $tree : 0;
            $path   = $k ? 'talents/icons' : 'hunterpettalents';

            $icons = DB::Aowow()->SelectCol($query, $what, $set, $subset);

            if (empty($icons))
                die('error: query for '.$v.' tree: '.$k.' empty');

            $res = imageCreateTrueColor(count($icons) * $dims, 2 * $dims);

            for($i = 0; $i < count($icons); $i++)
            {
                $im = @imagecreatefromjpeg('static/images/wow/icons/medium/'.strtolower($icons[$i]).'.jpg');
                if(!$im)
                    die('error: raw image '.$icons[$i]. ' not found');

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

                if (@!imagejpeg($res, 'static/images/wow/'.$path.'/'.$v.'_'.($tree + 1).'.jpg'))
                    die('error: '.$v.'_'.($tree + 1).'.jpg could not be written!');
            }
        }

        echo "textures for ".($k ? ucFirst($v) : "Pet")." done in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
