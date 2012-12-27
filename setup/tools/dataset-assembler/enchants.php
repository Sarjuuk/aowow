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

    include 'includes/class.spell.php';
    include 'includes/class.item.php';

    set_time_limit(300);

    // from g_item_slots: 13:"One-Hand", 26:"Ranged", 17:"Two-Hand",
    $slotPointer   = [13, 17, 26, 26, 13, 17, 17, 13, 17, null, 17, null, null, 13, null, 13, null, null, null, null, 17];
    $locales       = [LOCALE_EN, LOCALE_FR, LOCALE_DE, LOCALE_ES, LOCALE_RU];
    $enchantSpells = new SpellList([['effect1Id', '=', '53'], ['name_loc0', 'NOT LIKE', 'QA%']]);    // enchantItemPermanent && !qualityAssurance
    $castItems     = array();
    $jsonEnchants  = array();

    // check directory-structure
    foreach (Util::$localeStrings as $dir)
        if (!is_dir('datasets\\'.$dir))
            mkdir('datasets\\'.$dir, 0755, true);

    echo "script set up in ".Util::execTime()."<br>\n";

    foreach ($locales as $lId)
    {
        User::useLocale($lId);

        $enchantsOut = array();

        foreach ($enchantSpells->spellList as $spl)
        {
            $enchant = DB::Aowow()->SelectRow('SELECT * FROM ?_itemEnchantment WHERE Id = ?d', $spl->template['effect1MiscValue']);
            if (!$enchant)                                  // 'shouldn't' happen
                continue;

            // slots have to be recalculated
            $slot = 0;
            if ($spl->template['equippedItemClass'] == 4)   // armor
            {
                if ($invType = $spl->template['equippedItemInventoryTypeMask'])
                    $slot = $spl->template['equippedItemInventoryTypeMask'] >> 1;
                else  /* if (equippedItemSubClassMask == 64) */ // shields have it their own way <_<
                    $slot = (1 << (14 - 1));
            }
            else if ($spl->template['equippedItemClass'] == 2) // weapon
            {
                foreach ($slotPointer as $i => $sp)
                {
                    if (!$sp)
                        continue;

                    if ((1 << $i) & $spl->template['equippedItemSubClassMask'])
                    {
                        if ($sp == 13)                      // also mainHand & offHand *siiigh*
                            $slot |= ((1 << (21 - 1)) | (1 << (22 - 1)));

                        $slot |= (1 << ($sp - 1));
                    }
                }
            }

            // costy and locale-independant -> cache
            if (!isset($jsonEnchants[$enchant['Id']]))
                $jsonEnchants[$enchant['Id']] = Util::parseItemEnchantment($enchant);

            // defaults
            $ench = array(
                'name'        => [],                        // set by skill or item
                'quality'     => -1,                        // modified if item
                'icon'        => strToLower($spl->template['iconString']),  // item over spell
                'source'      => [],                        // <0: item; >0:spell
                'skill'       => -1,                        // modified if skill
                'slots'       => [],                        // determied per spell but set per item
                'enchantment' => Util::jsEscape(Util::localizedString($enchant, 'text')),
                'jsonequip'   => $jsonEnchants[$enchant['Id']],
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
            if ($skill = DB::Aowow()->SelectCell('SELECT skillId FROM ?_skill_line_ability WHERE spellId = ?d', $spl->Id))
            {
                $ench['name'][]   = Util::jsEscape(Util::localizedString($spl->template, 'name'));
                $ench['source'][] = $spl->Id;
                $ench['skill']    = $skill;
                $ench['slots'][]  = $slot;
            }

            // check if this item can be cast via item -> Source:Item
            if (!isset($castItems[$spl->Id]))
                $castItems[$spl->Id] = new ItemList([['spellid_1', '=', $spl->Id], ['name', 'NOT LIKE', 'Scroll of Enchant%']]);  // do not reuse enchantment scrolls

            foreach ($castItems[$spl->Id]->itemList as $item)
            {
                $ench['name'][]   = Util::jsEscape(Util::localizedString($item->template, 'name'));
                $ench['source'][] = -$item->Id;
                $ench['icon']     = strTolower($item->template['icon']);
                $ench['slots'][]  = $slot;

                if ($item->template['Quality'] > $ench['quality'])
                    $ench['quality'] = $item->template['Quality'];

                if ($item->template['AllowableClass'] > 0)
                {
                    $ench['classes'] = $item->template['AllowableClass'];
                    $ench['jsonequip']['classes'] = $item->template['AllowableClass'];
                }

                if (!isset($ench['jsonequip']['reqlevel']))
                    if ($item->template['RequiredLevel'] > 0)
                        $ench['jsonequip']['reqlevel'] = $item->template['RequiredLevel'];
            }

            // enchant spell not in use
            if (empty($ench['source']))
                continue;

            // everything gathered
            if (isset($enchantsOut[$enchant['Id']]))        // already found, append data
            {
                foreach ($enchantsOut[$enchant['Id']] as $k => $v)
                {
                    if (is_array($v))
                    {
                        while ($pop = array_pop($ench[$k]))
                            $enchantsOut[$enchant['Id']][$k][] = $pop;
                    }
                    else
                    {
                        if ($k == 'quality')                // quality:-1 if spells and items are mixed
                        {
                            if ($enchantsOut[$enchant['Id']]['source'][0] > 0 && $ench['source'][0] < 0)
                                $enchantsOut[$enchant['Id']][$k] = -1;
                            else if ($enchantsOut[$enchant['Id']]['source'][0] < 0 && $ench['source'][0] > 0)
                                $enchantsOut[$enchant['Id']][$k] = -1;
                            else
                                $enchantsOut[$enchant['Id']][$k] = $ench[$k];
                        }
                        else if ($enchantsOut[$enchant['Id']][$k] <= 0)
                            $enchantsOut[$enchant['Id']][$k] = $ench[$k];
                    }
                }
            }
            else                                            // nothing yet, create new
                $enchantsOut[$enchant['Id']] = $ench;
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
