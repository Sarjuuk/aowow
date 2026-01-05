<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $info = array(
        'classes' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: PlayerClass from dbc.']
    );

    protected $setupAfter     = [['icons'], []];
    protected $dbcSourceFiles = ['spell', 'charbaseinfo', 'skillraceclassinfo', 'skilllineability', 'chrclasses'];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::classes');

        $classes = DB::Aowow()->selectAssoc('SELECT *, `id` AS ARRAY_KEY FROM dbc_chrclasses');

        // add raceMask
        $races = DB::Aowow()->selectAssoc('SELECT `classId` AS ARRAY_KEY, BIT_OR(1 << (`raceId` - 1)) AS "raceMask" FROM dbc_charbaseinfo GROUP BY `classId`');
        Util::arraySumByKey($classes, $races);

        // add skills
        if ($skills = DB::Aowow()->selectCol('SELECT LOG(2, `classMask`) + 1 AS ARRAY_KEY, GROUP_CONCAT(`skillLine` SEPARATOR \' \') FROM dbc_skillraceclassinfo WHERE `flags` = %i GROUP BY `classMask` HAVING ARRAY_KEY = CAST(LOG(2, `classMask`) + 1 AS SIGNED)', 0x410))
            foreach ($skills as $classId => $skillStr)
                $classes[$classId]['skills'] = $skillStr;

        // collect iconIds
        $iconIds = DB::Aowow()->selectCol('SELECT `id`, `name` AS ARRAY_KEY FROM ::icons WHERE `name` IN %in', array_filter(array_map(fn($x) => 'class_'.strtolower($x['fileString']), $classes)));
        foreach ($classes AS $id => $class)
            $classes[$id]['iconId'] = $iconIds['class_'.strtolower($class['fileString'])] ?? 0;

        // add weaponTypeMask & armorTypeMask
        foreach ($classes as $id => &$data)
        {
            $mask = 1 << ($id - 1);
            $data['weaponTypeMask'] = DB::Aowow()->selectCell(
               'SELECT BIT_OR(`equippedItemSubClassMask`)
                FROM   dbc_spell s
                JOIN   dbc_skilllineability sla    ON sla.`spellId`    = s.`id`
                JOIN   dbc_skillraceclassinfo srci ON srci.`skillLine` = sla.`skillLineId` AND srci.`classMask` & %i
                WHERE  sla.`skilllineid` <> 183 AND (sla.`reqClassMask` & %i OR sla.`reqClassMask` = 0) AND `equippedItemClass` = %i AND (`effect1Id` = %i OR `effect2Id` = %i)',
                $mask, $mask, ITEM_CLASS_WEAPON, SPELL_EFFECT_PROFICIENCY, SPELL_EFFECT_PROFICIENCY
            );
            $data['armorTypeMask']  = DB::Aowow()->selectCell(
               'SELECT BIT_OR(`equippedItemSubClassMask`)
                FROM   dbc_spell s
                JOIN   dbc_skilllineability sla    ON sla.`spellId`    = s.`id`
                JOIN   dbc_skillraceclassinfo srci ON srci.`skillLine` = sla.`skillLineId` AND srci.`classMask` & %i
                WHERE  sla.`reqClassMask` & %i AND `equippedItemClass` = %i AND (`effect1Id` = %i OR `effect2Id` = %i)',
                $mask, $mask, ITEM_CLASS_ARMOR, SPELL_EFFECT_PROFICIENCY, SPELL_EFFECT_PROFICIENCY
            );
        }

        foreach ($classes as $cl)
            DB::Aowow()->qry('INSERT INTO ::classes %v', $cl);

        $this->reapplyCCFlags('classes', Type::CHR_CLASS);

        return true;
    }
});

?>
