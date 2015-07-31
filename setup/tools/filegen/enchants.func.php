<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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

    function enchants()
    {
        // from g_item_slots: 13:"One-Hand", 26:"Ranged", 17:"Two-Hand",
        $slotPointer   = [13, 17, 26, 26, 13, 17, 17, 13, 17, null, 17, null, null, 13, null, 13, null, null, null, null, 17];
        $castItems     = [];
        $successs      = true;
        $enchantSpells = new SpellList([['effect1Id', 53], ['name_loc0', 'QA%', '!'], CFG_SQL_LIMIT_NONE]);     // enchantItemPermanent && !qualityAssurance

        // check directory-structure
        foreach (Util::$localeStrings as $dir)
            if (!CLISetup::writeDir('datasets/'.$dir))
                $success = false;

        $enchIds = [];
        foreach ($enchantSpells->iterate() as $__)
            $enchIds[] = $enchantSpells->getField('effect1MiscValue');

        $enchantments = new EnchantmentList(array(['id', $enchIds], CFG_SQL_LIMIT_NONE));
        if ($enchantments->error)
        {
            CLISetup::log('Required table ?_itemenchantment seems to be empty! Leaving enchants()...', CLISetup::LOG_ERROR);
            CLISetup::log();
            return false;
        }

        foreach (CLISetup::$localeIds as $lId)
        {
            set_time_limit(120);

            User::useLocale($lId);
            Lang::load(Util::$localeStrings[$lId]);

            $enchantsOut = [];
            foreach ($enchantSpells->iterate() as $__)
            {
                $eId = $enchantSpells->getField('effect1MiscValue');
                if (!$enchantments->getEntry($eId))
                {
                    CLISetup::log(' * could not find enchantment #'.$eId.' referenced by spell #'.$enchantSpells->id, CLISetup::LOG_WARN);
                    continue;
                }

                // slots have to be recalculated
                $slot = 0;
                if ($enchantSpells->getField('equippedItemClass') == 4)   // armor
                {
                    if ($invType = $enchantSpells->getField('equippedItemInventoryTypeMask'))
                        $slot = $invType >> 1;
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
                            if ($sp == 13)                  // also mainHand & offHand *siiigh*
                                $slot |= ((1 << (21 - 1)) | (1 << (22 - 1)));

                            $slot |= (1 << ($sp - 1));
                        }
                    }
                }


                // defaults
                $ench = array(
                    'name'        => [],                    // set by skill or item
                    'quality'     => -1,                    // modified if item
                    'icon'        => strToLower($enchantSpells->getField('iconString')),  // item over spell
                    'source'      => [],                    // <0: item; >0:spell
                    'skill'       => -1,                    // modified if skill
                    'slots'       => [],                    // determined per spell but set per item
                    'enchantment' => $enchantments->getField('name', true),
                    'jsonequip'   => $enchantments->getStatGain(),
                    'temp'        => 0,                     // always 0
                    'classes'     => 0,                     // modified by item
                );

                if ($_ = $enchantments->getField('skillLine'))
                    $ench['jsonequip']['reqskill'] = $_;

                if ($_ = $enchantments->getField('skillLevel'))
                    $ench['jsonequip']['reqskillrank'] = $_;

                if (($_ = $enchantments->getField('requiredLevel')) && $_ > 1)
                    $ench['jsonequip']['reqlevel'] = $_;

                // check if the spell has an entry in skill_line_ability -> Source:Profession
                if ($skills = $enchantSpells->getField('skillLines'))
                {
                    $ench['name'][]   = $enchantSpells->getField('name', true);
                    $ench['source'][] = $enchantSpells->id;
                    $ench['skill']    = $skills[0];
                    $ench['slots'][]  = $slot;
                }

                // check if this spell can be cast via item -> Source:Item
                if (!isset($castItems[$enchantSpells->id]))
                    $castItems[$enchantSpells->id] = new ItemList([['spellId1', $enchantSpells->id], ['name_loc0', 'Scroll of Enchant%', '!']]);    // do not reuse enchantment scrolls

                $cI = &$castItems[$enchantSpells->id];      // this construct is a bit .. unwieldy
                foreach ($cI->iterate() as $__)
                {
                    $ench['name'][]   = $cI->getField('name', true);
                    $ench['source'][] = -$cI->id;
                    $ench['icon']     = strTolower($cI->getField('iconString'));
                    $ench['slots'][]  = $slot;

                    if ($cI->getField('quality') > $ench['quality'])
                        $ench['quality'] = $cI->getField('quality');

                    if ($cI->getField('requiredClass') > 0)
                    {
                        $ench['classes'] = $cI->getField('requiredClass');
                        $ench['jsonequip']['classes'] = $cI->getField('requiredClass');
                    }

                    if (!isset($ench['jsonequip']['reqlevel']))
                        if ($cI->getField('requiredLevel') > 0)
                            $ench['jsonequip']['reqlevel'] = $cI->getField('requiredLevel');
                }

                // enchant spell not in use
                if (empty($ench['source']))
                    continue;

                // everything gathered
                if (isset($enchantsOut[$eId]))              // already found, append data
                {
                    foreach ($enchantsOut[$eId] as $k => $v)
                    {
                        if (is_array($v))
                        {
                            while ($pop = array_pop($ench[$k]))
                                $enchantsOut[$eId][$k][] = $pop;
                        }
                        else
                        {
                            if ($k == 'quality')            // quality:-1 if spells and items are mixed
                            {
                                if ($enchantsOut[$eId]['source'][0] > 0 && $ench['source'][0] < 0)
                                    $enchantsOut[$eId][$k] = -1;
                                else if ($enchantsOut[$eId]['source'][0] < 0 && $ench['source'][0] > 0)
                                    $enchantsOut[$eId][$k] = -1;
                                else
                                    $enchantsOut[$eId][$k] = $ench[$k];
                            }
                            else if ($enchantsOut[$eId][$k] <= 0)
                                $enchantsOut[$eId][$k] = $ench[$k];
                        }
                    }
                }
                else                                        // nothing yet, create new
                    $enchantsOut[$eId] = $ench;
            }

            // walk over each entry and strip single-item arrays
            foreach ($enchantsOut as &$ench)
                foreach ($ench as $k => $v)
                    if (is_array($v) && count($v) == 1 && $k != 'jsonequip')
                        $ench[$k] = $v[0];

            $toFile = "var g_enchants = ".Util::toJSON($enchantsOut).";";
            $file   = 'datasets/'.User::$localeString.'/enchants';

            if (!CLISetup::writeFile($file, $toFile))
                $success = false;
        }

        return $successs;
    }

?>
