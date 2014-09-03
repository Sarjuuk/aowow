<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'glyphs'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // GlyphProperties, Spells, SkillLineAbility

    /* Example
        40896: {
            "name":"Glyph of Frenzied Regeneration",
            "description":"For 6 sec after activating Frenzied Regeneration, healing effects on you are 40% more powerful.  However, your Frenzied Regeneration now always costs 60 Rage and no longer converts Rage into health.",
            "icon":"ability_bullrush",
            "type":1,
            "classs":11,
            "skill":798,
            "level":25,
        },
    */

    $queryGlyphs = '
        SELECT
            i.id AS itemId,
            i.*,
            IF (g.typeFlags & 0x1, 2, 1) AS type,
            i.subclass AS classs,
            i.requiredLevel AS level,
            s1.Id AS glyphSpell,
            s1.iconStringAlt AS icon,
            s1.skillLine1 AS skillId,
            s2.Id AS glyphEffect,
            s2.Id AS ARRAY_KEY
        FROM
            ?_items i
        JOIN
            ?_spell s1 ON s1.Id = i.spellid1
        JOIN
            ?_glyphproperties g ON g.Id = s1.effect1MiscValue
        JOIN
            ?_spell s2 ON s2.Id = g.spellId
        WHERE
            i.classBak = 16
        ;
    ';

    $glyphList = DB::Aowow()->Select($queryGlyphs);
    $locales   = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets/'.$dir))
            mkdir('datasets/'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    $glyphSpells = new SpellList(array(['s.id', array_keys($glyphList)], CFG_SQL_LIMIT_NONE));

    foreach ($locales as $lId)
    {
        User::useLocale($lId);
        Lang::load(Util::$localeStrings[$lId]);

        $glyphsOut = [];
        foreach ($glyphSpells->iterate() as $__)
        {
            $pop = $glyphList[$glyphSpells->id];

            if (!$pop['glyphEffect'])
                continue;

            if ($glyphSpells->getField('effect1Id') != 6 && $glyphSpells->getField('effect2Id') != 6 && $glyphSpells->getField('effect3Id') != 6)
                continue;

            $glyphsOut[$pop['itemId']] = array(
                'name'        => Util::localizedString($pop, 'name'),
                'description' => $glyphSpells->parseText()[0],
                'icon'        => $pop['icon'],
                'type'        => $pop['type'],
                'classs'      => $pop['classs'],
                'skill'       => $pop['skillId'],
                'level'       => $pop['level']
            );
        }

        $toFile  = "var g_glyphs = ";
        $toFile .= json_encode($glyphsOut, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets/'.User::$localeString.'/glyphs';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done glyphs loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    Lang::load(Util::$localeStrings[LOCALE_EN]);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);
?>
