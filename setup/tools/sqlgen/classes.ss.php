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

    protected $dbcSourceFiles = ['spell', 'charbaseinfo', 'skillraceclassinfo', 'skilllineability', 'chrclasses'];

    public function generate(array $ids = []) : bool
    {
        DB::Aowow()->query('TRUNCATE ?_classes');

        $classes = DB::Aowow()->select('SELECT *, `id` AS ARRAY_KEY FROM dbc_chrclasses');

        // add raceMask
        $races = DB::Aowow()->select('SELECT `classId` AS ARRAY_KEY, BIT_OR(1 << (`raceId` - 1)) AS "raceMask" FROM dbc_charbaseinfo GROUP BY `classId`');
        Util::arraySumByKey($classes, $races);

        // add skills
        if ($skills = DB::Aowow()->selectCol('SELECT LOG(2, `classMask`) + 1 AS ARRAY_KEY, GROUP_CONCAT(`skillLine` SEPARATOR \' \') FROM dbc_skillraceclassinfo WHERE `flags` = ?d GROUP BY `classMask` HAVING ARRAY_KEY = CAST(LOG(2, `classMask`) + 1 AS SIGNED)', 0x410))
            foreach ($skills as $classId => $skillStr)
                $classes[$classId]['skills'] = $skillStr;

        // add weaponTypeMask & armorTypeMask
        foreach ($classes as $id => &$data)
        {
            $mask = 1 << ($id - 1);
            $data['weaponTypeMask'] = DB::Aowow()->selectCell(
               'SELECT BIT_OR(`equippedItemSubClassMask`)
                FROM   dbc_spell s
                JOIN   dbc_skilllineability sla    ON sla.`spellId`    = s.`id`
                JOIN   dbc_skillraceclassinfo srci ON srci.`skillLine` = sla.`skillLineId` AND srci.`classMask` & ?d
                WHERE  sla.`skilllineid` <> 183 AND (sla.`reqClassMask` & ?d OR sla.`reqClassMask` = 0) AND `equippedItemClass` = ?d AND (`effect1Id` = ?d OR `effect2Id` = ?d)',
                $mask, $mask, ITEM_CLASS_WEAPON, SPELL_EFFECT_PROFICIENCY, SPELL_EFFECT_PROFICIENCY
            );
            $data['armorTypeMask']  = DB::Aowow()->selectCell(
               'SELECT BIT_OR(`equippedItemSubClassMask`)
                FROM   dbc_spell s
                JOIN   dbc_skilllineability sla    ON sla.`spellId`    = s.`id`
                JOIN   dbc_skillraceclassinfo srci ON srci.`skillLine` = sla.`skillLineId` AND srci.`classMask` & ?d
                WHERE  sla.`reqClassMask` & ?d AND `equippedItemClass` = ?d AND (`effect1Id` = ?d OR `effect2Id` = ?d)',
                $mask, $mask, ITEM_CLASS_ARMOR, SPELL_EFFECT_PROFICIENCY, SPELL_EFFECT_PROFICIENCY
            );
        }

        foreach ($classes as $cl)
            DB::Aowow()->query('INSERT INTO ?_classes (?#) VALUES (?a)', array_keys($cl), array_values($cl));

        $this->reapplyCCFlags('classes', Type::CHR_CLASS);

        return true;
    }
});

?>
