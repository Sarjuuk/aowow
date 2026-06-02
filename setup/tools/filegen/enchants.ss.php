<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


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
        gearscore:20 // fuck, i'm doing this..
    },
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'enchants' => [[], CLISetup::ARGV_PARAM, 'Compiles enchantment effects to file for the item comparison tool and profiler tool.']
    );

    protected $setupAfter   = [['items', 'spell', 'itemenchantment', 'icons', 'source'], []];
    protected $requiredDirs = ['datasets/'];
    protected $localized    = true;

    public function generate() : bool
    {
        // from g_item_slots: 13:"One-Hand", 15:"Ranged", 17:"Two-Hand",
        $slotPointer   = [13, 17, 15, 15, 13, 17, 17, 13, 17, null, 17, null, null, 13, null, 13, null, null, null, null, 17];

        $enchantSpells = DB::Aowow()->selectAssoc(
           'SELECT    s.`id` AS ARRAY_KEY,
                      `effect1MiscValue`,
                      `equippedItemClass`, `equippedItemInventoryTypeMask`, `equippedItemSubClassMask`,
                      `skillLine1`,
                      IFNULL(i.`name`, %s) AS "iconString",
                      `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`
            FROM      ::spell s
            LEFT JOIN ::icons i ON i.`id` = s.`iconId`
            WHERE     `effect1Id` = %i AND
                      `name_loc0` NOT LIKE "QA%"',
            DEFAULT_ICON, SPELL_EFFECT_ENCHANT_ITEM
        );

        $enchIds = array_column($enchantSpells, 'effect1MiscValue');

        $enchantments = new EnchantmentContainer(array(['id', $enchIds]));
        if ($enchantments->error)
        {
            CLI::write('[enchants] Required table ::itemenchantment seems to be empty!', CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        $castItems = new ItemContainer(array(['spellId1', array_keys($enchantSpells)], ['src.typeId', null, '!']));
        if ($castItems->error)
        {
            CLI::write('[enchants] Required table ::items seems to be empty!', CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        foreach (CLISetup::$locales as $loc)
        {
            Lang::load($loc);

            $enchantsOut = [];
            foreach ($enchantSpells as $esId => $es)
            {
                set_time_limit(5);

                $eId = $es['effect1MiscValue'];
                if (!($eEntry = $enchantments->getEntry($eId)))
                {
                    CLI::write('[enchants] * could not find enchantment #'.$eId.' referenced by spell #'.$esId, CLI::LOG_WARN);
                    continue;
                }

                // slots have to be recalculated
                $slot = 0;
                if ($es['equippedItemClass'] == ITEM_CLASS_ARMOR)
                {
                    if ($invType = $es['equippedItemInventoryTypeMask'])
                        $slot = $invType >> 1;
                    else  /* if (equippedItemSubClassMask == 64) */ // shields have it their own way <_<
                        $slot = (1 << (INVTYPE_SHIELD - 1));
                }
                else if ($es['equippedItemClass'] == ITEM_CLASS_WEAPON)
                {
                    foreach ($slotPointer as $i => $sp)
                    {
                        if (!$sp)
                            continue;

                        if ((1 << $i) & $es['equippedItemSubClassMask'])
                        {
                            if ($sp == 13)                  // also mainHand & offHand *siiigh*
                                $slot |= ((1 << (INVTYPE_WEAPONMAINHAND - 1)) | (1 << (INVTYPE_WEAPONOFFHAND - 1)));

                            $slot |= (1 << ($sp - 1));
                        }
                    }
                }

                // defaults
                $ench = array(
                    'name'        => [],                    // set by skill or item
                    'quality'     => -1,                    // modified if item
                    'icon'        => strToLower($es['iconString']),  // item over spell
                    'source'      => [],                    // <0: item; >0:spell
                    'skill'       => -1,                    // modified if skill
                    'slots'       => [],                    // determined per spell but set per item
                    'enchantment' => $eEntry->name,
                    'jsonequip'   => $eEntry->getStatGain(),
                    'temp'        => 0,                     // always 0
                    'classes'     => 0,                     // modified by item
                    'gearscore'   => 0                      // set later
                );

                if ($_ = $eEntry->skillLine)
                    $ench['jsonequip']['reqskill'] = $_;

                if ($_ = $eEntry->skillLevel)
                    $ench['jsonequip']['reqskillrank'] = $_;

                if (($_ = $eEntry->requiredLevel) && $_ > 1)
                    $ench['jsonequip']['reqlevel'] = $_;

                // check if the spell has an entry in skill_line_ability -> Source:Profession
                if ($es['skillLine1'])
                {
                    $ench['name'][]   = Util::localizedString($es, 'name');
                    $ench['source'][] = $esId;
                    $ench['skill']    = $es['skillLine1'];
                    $ench['slots'][]  = $slot;
                }

                // check if this spell can be cast via item -> Source:Item
                // do not reuse enchantment scrolls; do not use items without source
                foreach ($castItems->iterate() as $iEntry)
                {
                    if ($eEntry->skillLevel)
                        if ($s = Util::getEnchantmentScore(0, -1, true, $eId))
                            $ench['gearscore'] = $s;

                    if ($iEntry->spells[0][0] != $esId)
                        continue;

                    $isScroll = $iEntry->class == ITEM_CLASS_CONSUMABLE && $iEntry->subClass == ITEM_SUBCLASS_ITEM_ENHANCEMENT && $iEntry->pickUpSoundId == 1192;

                    if ($s = Util::getEnchantmentScore($iEntry->itemLevel, $isScroll ? -1 : $iEntry->quality, !!$eEntry->skillLevel, $eId))
                        if ($s > $ench['gearscore'])
                            $ench['gearscore'] = $s;

                    if ($isScroll)
                        continue;

                    $ench['name'][]   = $iEntry->name;
                    $ench['source'][] = -$iEntry->id;
                    $ench['icon']     = $iEntry->icon;
                    $ench['slots'][]  = $slot;

                    if ($iEntry->quality > $ench['quality'])
                        $ench['quality'] = $iEntry->quality;

                    if ($rc = $iEntry->requiredClass)
                    {
                        $ench['classes'] = $rc;
                        $ench['jsonequip']['classes'] = $rc;
                    }

                    if (!isset($ench['jsonequip']['reqlevel']))
                        if ($iEntry->requiredLevel > 0)
                            $ench['jsonequip']['reqlevel'] = $iEntry->requiredLevel;
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
            $file   = 'datasets/'.$loc->json().'/enchants';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }

        return $this->success;
    }
});

?>
