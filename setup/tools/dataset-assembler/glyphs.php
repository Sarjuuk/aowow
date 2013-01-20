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

    $queryIcons = '
        SELECT
            s.Id,
            name_loc0 as name,
            sk.classMask,
            sk.skillId,
            s.iconString as icon
        FROM
            aowow_spell s
        JOIN
            aowow_skill_line_ability sk ON
                sk.spellId = s.Id
        WHERE
            [WHERE]
        LIMIT
            1
    ';

    $queryGlyphs = '
        SELECT
            i.entry as itemId,
            i.name,
            li.*,
            IF (g.typeFlags & 0x1, 2, 1) as type,
            i.subclass as classs,
            i.requiredLevel as level,
            s1.Id as glyphSpell,
            s2.Id as glyphEffect
        FROM
            item_template i
        LEFT JOIN
            locales_item li ON
                i.entry = li.entry
        LEFT JOIN
            ?_spell s1 ON
                s1.Id = i.spellid_1
        LEFT JOIN
            ?_glyphProperties g ON
                g.Id = s1.effect1MiscValue
        LEFT JOIN
            ?_spell s2 ON
                s2.Id = g.spellId
        WHERE
            i.class = 16
        ;
    ';

    $class2Family = array(
        8 => 3,     // Mage
        1 => 4,     // Warrior
        9 => 5,     // Warlock
        5 => 6,     // Priest
       11 => 7,     // Druid
        4 => 8,     // Rogue
        3 => 9,     // Hunter
        2 => 10,    // Paladin
        7 => 11,    // Shaman
        6 => 15,    // Death Knight
    );

    // generic rules are nowhere to be found :(
    $spellNameHelp = array(
    // itemId => correctNameString/spellId
        43362 => 'Polymorph',
        50077 => 'Corruption',
        50125 => 'Rejuvenation',
        45805 => 'Pestilence',
        45804 => 'Death Coil',
        45793 => 50720,                                     // Vigilance - no SpellFamilyId
        44928 => 'Starfall',
        43549 => 'Raise Dead',
        43539 => 'Death Coil',
        43430 => 'Thunder Clap',
        43425 => 'Shield Slam',
        43423 => 'Rend',
        43420 => 'Mocking Blow',
        43416 => 'Execute',
        43414 => 'Cleave',
        43413 => 'Charge',
        43400 => 'Victory Rush',
        43394 => 'Ritual of Souls',
        43385 => 'Reincarnation',
        43379 => 'Sprint',
        43361 => 'Polymorph',
        43354 => 'Eyes of the Beast',
        43342 => 'Fade',
        43331 => 'Rebirth',
        42900 => 'Mend Pet',
        42469 => 63108,                                     // Siphon Life is passive <_<
        42417 => 20711,                                     // Spirit of Redemption .. another false passive :/
        42407 => 'Shadow Form',
        41524 => 'lava burst',
        41108 => 'Lay on Hands'
    );

    include 'includes/class.spell.php';

    set_time_limit(300);

    $glyphList = DB::Aowow()->Select($queryGlyphs);
    $locales   = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        $glyphsOut = [];

        foreach ($glyphList as $pop)
        {
            if (!$pop['glyphEffect'])
                continue;

            $spl = new Spell($pop['glyphEffect']);

            if ($spl->template['effect1Id'] != 6)
                continue;

            if ($pop['itemId'] == 42958)                    // Crippling Poison has no skillLine.. oO => hardcode
            {
                $glyphsOut[$pop['itemId']] = array(
                    'name'        => Util::jsEscape(Util::localizedString($pop, 'name')),
                    'description' => Util::jsEscape($spl->parseText()),
                    'icon'        => 'ability_poisonsting',
                    'type'        => 0,
                    'classs'      => $pop['classs'],
                    'skill'       => 253,
                    'level'       => $pop['level']
                );

                continue;
            }

            $description = $spl->parseText();
            $spellFamily = $class2Family[$pop['classs']];
            $classId     = $pop['classs'] - 1;
            $skill       = 0;
            $icon        = '';

            $search = @$spellNameHelp[$pop['itemId']] ? $spellNameHelp[$pop['itemId']] : '%'.str_replace('Glyph of ', '', $pop['name']).'%';
            if (is_int($search))
                $where = "?d AND s.id = ?d";
            else
                $where = "sk.skillID <> 0 AND SpellFamilyId = ?d AND name_loc0 LIKE ?s AND (attributes0 & 0x40) = 0";

            $icons = DB::Aowow()->Select(
                str_replace('[WHERE]', $where, $queryIcons),
                $spellFamily,
                $search
            );

            $l = [null, 'A', 'B', 'C'];
            $i = 0;
            while (empty($icons) && $i < 3)
            {
                $i++;
                $m1 = $spl->template['effect1SpellClassMask'.$l[$i]];
                $m2 = $spl->template['effect2SpellClassMask'.$l[$i]];
                $m3 = $spl->template['effect3SpellClassMask'.$l[$i]];

                if ($spl->template['effect'.$i.'Id'] != 6 || (!$m1 && !$m2 && !$m3))
                    continue;

                $where = "SpellFamilyId = ?d AND ((SpellFamilyFlags3 & 0xFFFFFFFF) & ?d OR (SpellFamilyFlags2 & 0xFFFFFFFF) & ?d OR (SpellFamilyFlags1 & 0xFFFFFFFF) & ?d)";
                $icons = DB::Aowow()->Select(
                    str_replace ('[WHERE]', $where, $queryIcons),
                    $spellFamily,
                    $m1,
                    $m2,
                    $m3
                );
            }

            while ($iPop = array_pop($icons))
            {
                $skill = $iPop['skillId'];
                $icon  = $iPop['icon'];
            }

            $glyphsOut[$pop['itemId']] = array(
                'name'        => Util::jsEscape(Util::localizedString($pop, 'name')),
                'description' => Util::jsEscape($description),
                'icon'        => strToLower($icon),
                'type'        => $pop['type'],
                'classs'      => $pop['classs'],
                'skill'       => $skill,
                'level'       => $pop['level']
            );
        }

        $toFile  = "var g_glyphs = ";
        $toFile .= json_encode($glyphsOut, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets\\'.User::$localeString.'\\glyphs';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done glyphs loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    User::useLocale(LOCALE_EN);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
