<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


    // Create 'enchants'-file for available locales
    // this script requires the following dbc-files to be parsed and available
    // Spells, SkillLineAbility, SpellItemEnchantment

    /* Examples
        15: {
            name:'Leichtes Rüstungsset',
            quality:1,
            icon:'INV_Misc_ArmorKit_17',
            source:-2304,
            skill:-1,
            slots:525008,
            enchantment:'Verstärkt (+8 Rüstung)',
            jsonequip:{"armor":8,"reqlevel":1},
            temp:0,
            classes:0,
            gearscore:1
        },
        2928: {
            name:'Ring - Zaubermacht',
            quality:-1,
            icon:'INV_Misc_Note_01',
            source:27924,
            skill:333,
            slots:1024,
            enchantment:'+12 Zaubermacht',
            jsonequip:{"splpwr":12},
            temp:0,
            classes:0,
            gearscore:15
        },
        3231: {
            name:['Handschuhe - Waffenkunde','Armschiene - Waffenkunde'],
            quality:-1,
            icon:'spell_holy_greaterheal',
            source:[44484,44598],
            skill:333,
            slots:[512,256],
            enchantment:'+15 Waffenkundewertung',
            jsonequip:{reqlevel:60,"exprtng":15},
            temp:0,
            classes:0,
            gearscore:20 // nope, i'm not doing this
        },
    */

    set_time_limit(300);

    // from g_item_slots: 13:"One-Hand", 26:"Ranged", 17:"Two-Hand",
    $slotPointer   = [13, 17, 26, 26, 13, 17, 17, 13, 17, null, 17, null, null, 13, null, 13, null, null, null, null, 17];
    $locales       = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $enchantSpells = new SpellList([['effect1Id', 53], ['name_loc0', 'QA%', '!']]);     // enchantItemPermanent && !qualityAssurance
    $castItems     = [];
    $jsonEnchants  = [];

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        $enchantsOut = [];

        while ($enchantSpells->iterate())
        {
            $enchant = DB::Aowow()->SelectRow('SELECT * FROM ?_itemEnchantment WHERE Id = ?d', $enchantSpells->getField('effect1MiscValue'));
            if (!$enchant)                                  // 'shouldn't' happen
                continue;

            // slots have to be recalculated
            $slot = 0;
            if ($enchantSpells->getField('equippedItemClass') == 4)   // armor
            {
                if ($invType = $enchantSpells->getField('equippedItemInventoryTypeMask'))
                    $slot = $enchantSpells->getField('equippedItemInventoryTypeMask') >> 1;
                else  /* if (equippedItemSubClassMask == 64) */ // shields have it their own way <_<
                    $slot = (1 << (14 - 1));
            }
            else if ($enchantSpells->getField('equippedItemClass') == 2) // weapon
            {
                foreach ($slotPointer as $i => $sp)
                {
                    if (!$sp)
                        continue;

                    if ((1 << $i) & $enchantSpells->getField('equippedItemSubClassMask'))
                    {
                        if ($sp == 13)                      // also mainHand & offHand *siiigh*
                            $slot |= ((1 << (21 - 1)) | (1 << (22 - 1)));

                        $slot |= (1 << ($sp - 1));
                    }
                }
            }

            // costy and locale-independant -> cache
            if (!isset($jsonEnchants[$enchant['id']]))
                $jsonEnchants[$enchant['id']] = Util::parseItemEnchantment($enchant);

            // defaults
            $ench = array(
                'name'        => [],                        // set by skill or item
                'quality'     => -1,                        // modified if item
                'icon'        => strToLower($enchantSpells->getField('iconString')),  // item over spell
                'source'      => [],                        // <0: item; >0:spell
                'skill'       => -1,                        // modified if skill
                'slots'       => [],                        // determied per spell but set per item
                'enchantment' => Util::jsEscape(Util::localizedString($enchant, 'text')),
                'jsonequip'   => $jsonEnchants[$enchant['id']],
                'temp'        => 0,                         // always 0
                'classes'     => 0,                         // modified by item
            );

            if ($enchant['skillLine'] > 0)
                $ench['jsonequip']['reqskill'] = $enchant['skillLine'];

            if ($enchant['skillLevel'] > 0)
                $ench['jsonequip']['reqskillrank'] = $enchant['skillLevel'];

            if ($enchant['requiredLevel'] > 0)
                $ench['jsonequip']['reqlevel'] = $enchant['requiredLevel'];

            // check if the spell has an entry in skill_line_ability -> Source:Profession
            if ($skill = DB::Aowow()->SelectCell('SELECT skillLineId FROM dbc.skillLineAbility WHERE spellId = ?d', $enchantSpells->id))
            {
                $ench['name'][]   = $enchantSpells->getField('name', true);
                $ench['source'][] = $enchantSpells->id;
                $ench['skill']    = $skill;
                $ench['slots'][]  = $slot;
            }

            // check if this item can be cast via item -> Source:Item
            if (!isset($castItems[$enchantSpells->id]))
                $castItems[$enchantSpells->id] = new ItemList([['spellid_1', $enchantSpells->id], ['name', 'Scroll of Enchant%', '!']]);    // do not reuse enchantment scrolls

            $cI = &$castItems[$enchantSpells->id];          // this construct is a bit .. unwieldy
            while ($cI->iterate())
            {
                $ench['name'][]   = $cI->getField('name', true);
                $ench['source'][] = -$cI->id;
                $ench['icon']     = strTolower($cI->getField('icon'));
                $ench['slots'][]  = $slot;

                if ($cI->getField('Quality') > $ench['quality'])
                    $ench['quality'] = $cI->getField('Quality');

                if ($cI->getField('AllowableClass') > 0)
                {
                    $ench['classes'] = $cI->getField('AllowableClass');
                    $ench['jsonequip']['classes'] = $cI->getField('AllowableClass');
                }

                if (!isset($ench['jsonequip']['reqlevel']))
                    if ($cI->getField('RequiredLevel') > 0)
                        $ench['jsonequip']['reqlevel'] = $cI->getField('RequiredLevel');
            }

            // enchant spell not in use
            if (empty($ench['source']))
                continue;

            // everything gathered
            if (isset($enchantsOut[$enchant['id']]))        // already found, append data
            {
                foreach ($enchantsOut[$enchant['id']] as $k => $v)
                {
                    if (is_array($v))
                    {
                        while ($pop = array_pop($ench[$k]))
                            $enchantsOut[$enchant['id']][$k][] = $pop;
                    }
                    else
                    {
                        if ($k == 'quality')                // quality:-1 if spells and items are mixed
                        {
                            if ($enchantsOut[$enchant['id']]['source'][0] > 0 && $ench['source'][0] < 0)
                                $enchantsOut[$enchant['id']][$k] = -1;
                            else if ($enchantsOut[$enchant['id']]['source'][0] < 0 && $ench['source'][0] > 0)
                                $enchantsOut[$enchant['id']][$k] = -1;
                            else
                                $enchantsOut[$enchant['id']][$k] = $ench[$k];
                        }
                        else if ($enchantsOut[$enchant['id']][$k] <= 0)
                            $enchantsOut[$enchant['id']][$k] = $ench[$k];
                    }
                }
            }
            else                                            // nothing yet, create new
                $enchantsOut[$enchant['id']] = $ench;
        }

        // walk over each entry and strip single-item arrays
        foreach ($enchantsOut as $eId => $ench)
        {
            foreach ($ench as $k => $v)
                if (is_array($v) && count($v) == 1 && $k != 'jsonequip')
                    $enchantsOut[$eId][$k] = $v[0];
        }

        ksort($enchantsOut);

        $toFile  = "var g_enchants = ";
        $toFile .= json_encode($enchantsOut, JSON_PRETTY_PRINT | JSON_NUMERIC_CHECK);
        $toFile .= ";";
        $file    = 'datasets\\'.User::$localeString.'\\enchants';

        $handle = fOpen($file, "w");
        fWrite($handle, $toFile);
        fClose($handle);

        echo "done enchants loc: ".$lId." in ".Util::execTime()."<br>\n";
    }

    echo "<br>\nall done";

    User::useLocale(LOCALE_EN);

    $stats = DB::Aowow()->getStatistics();
    echo "<br>\n".$stats['count']." queries in: ".Util::formatTime($stats['time'] * 1000);

?>
