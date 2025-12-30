<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* Example
    22460: {
        name:'Prismatic Sphere',
        quality:3,
        icon:'INV_Enchant_PrismaticSphere',
        enchantment:'+3 Resist All',
        jsonequip:{"arcres":3,"avgbuyout":242980,"firres":3,"frores":3,"holres":3,"natres":3,"shares":3},
        colors:14,
        expansion:1
        gearscore:8  // if only..
    },
*/

CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'gems' => [[], CLISetup::ARGV_PARAM, 'Compiles gems to file for the item comparison tool and profiler tool.']
    );

    protected $setupAfter   = [['items', 'itemenchantment', 'icons'], []];
    protected $requiredDirs = ['datasets/'];
    protected $localized    = true;

    public function generate() : bool
    {
        // sketchy, but should work
        // id < 36'000 || ilevel < 70 ? BC : WOTLK
        $gems = DB::Aowow()->select(
           'SELECT    i.`id` AS "itemId",
                      i.`name_loc0`, i.`name_loc2`, i.`name_loc3`, i.`name_loc4`, i.`name_loc6`, i.`name_loc8`,
                      IF (i.`id` < 36000 OR i.`itemLevel` < 70, ?d, ?d) AS "expansion",
                      i.`quality`,
                      ic.`name` AS "icon",
                      i.`gemEnchantmentId` AS "enchId",
                      i.`gemColorMask` AS "colors",
                      i.`requiredSkill`,
                      i.`itemLevel`
            FROM      ?_items i
            JOIN      ?_icons ic ON ic.`id` = i.`iconId`
            WHERE     i.`gemEnchantmentId` <> 0
            ORDER BY  i.`id` DESC',
            EXP_BC, EXP_WOTLK
        );

        $enchantments = new EnchantmentList(array(['id', array_column($gems, 'enchId')]));
        if ($enchantments->error)
        {
            CLI::write('[gems] Required table ?_itemenchantment seems to be empty!', CLI::LOG_ERROR);
            CLI::write();
            return false;
        }

        foreach (CLISetup::$locales as $loc)
        {
            set_time_limit(5);

            Lang::load($loc);

            $gemsOut = [];
            foreach ($gems as $g)
            {
                if (!$enchantments->getEntry($g['enchId']))
                {
                    CLI::write('[gems] * could not find enchantment #'.$g['enchId'].' referenced by item #'.$g['itemId'], CLI::LOG_WARN);
                    continue;
                }

                $gemsOut[$g['itemId']] = array(
                    'name'        => Util::localizedString($g, 'name'),
                    'quality'     => $g['quality'],
                    'icon'        => strToLower($g['icon']),
                    'enchantment' => $enchantments->getField('name', true),
                    'jsonequip'   => $enchantments->getStatGainForCurrent(),
                    'colors'      => $g['colors'],
                    'expansion'   => $g['expansion'],
                    'gearscore'   => Util::getGemScore($g['itemLevel'], $g['quality'], $g['requiredSkill'] == SKILL_JEWELCRAFTING, $g['itemId'])
                );
            }

            $toFile = "var g_gems = ".Util::toJSON($gemsOut).";";
            $file   = 'datasets/'.$loc->json().'/gems';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }

        return $this->success;
    }
});

?>
