<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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

    // Create 'glyphs'-file for available locales
    // this script requires the following dbc-files to be parsed and available

    function glyphs()
    {
        $success   = true;
        $glyphList = DB::Aowow()->Select(
           'SELECT i.id AS itemId,
                   i.*,
                   IF (g.typeFlags & 0x1, 2, 1) AS type,
                   i.subclass AS classs,
                   i.requiredLevel AS level,
                   s1.id AS glyphSpell,
                   ic.name AS icon,
                   s1.skillLine1 AS skillId,
                   s2.id AS glyphEffect,
                   s2.id AS ARRAY_KEY
            FROM   ?_items i
            JOIN   ?_spell s1 ON s1.id = i.spellid1
            JOIN   ?_glyphproperties g ON g.id = s1.effect1MiscValue
            JOIN   ?_spell s2 ON s2.id = g.spellId
            JOIN   ?_icons ic ON ic.id = s1.iconIdAlt
            WHERE  i.classBak = 16');

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        $glyphSpells = new SpellList(array(['s.id', array_keys($glyphList)], CFG_SQL_LIMIT_NONE));

        foreach (CLISetup::$localeIds as $lId)
        {
            set_time_limit(30);

            User::useLocale($lId);
            Lang::load($lId);

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

            $toFile = "var g_glyphs = ".Util::toJSON($glyphsOut).";";
            $file   = 'datasets/'.User::$localeString.'/glyphs';

            if (!CLISetup::writeFile($file, $toFile))
                $success = false;
        }

        return $success;
    }
?>
