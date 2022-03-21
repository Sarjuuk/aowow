<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'classes';

    protected $dbcSourceFiles = ['spell', 'charbaseinfo', 'skillraceclassinfo', 'skilllineability', 'chrclasses'];

    public function generate(array $ids = []) : bool
    {
        $classes = DB::Aowow()->select('SELECT *, id AS ARRAY_KEY FROM dbc_chrclasses');

        // add raceMask
        $races = DB::Aowow()->select('SELECT classId AS ARRAY_KEY, BIT_OR(1 << (raceId - 1)) AS raceMask FROM dbc_charbaseinfo GROUP BY classId');
        Util::arraySumByKey($classes, $races);

        // add skills
        $skills = DB::Aowow()->select('SELECT LOG(2, classMask) + 1 AS ARRAY_KEY, GROUP_CONCAT(skillLine SEPARATOR \' \') AS skills FROM dbc_skillraceclassinfo WHERE flags = 1040 GROUP BY classMask HAVING ARRAY_KEY = CAST(LOG(2, classMask) + 1 AS SIGNED)');
        Util::arraySumByKey($classes, $skills);

        // add weaponTypeMask & armorTypeMask
        foreach ($classes as $id => &$data)
        {
            $data['weaponTypeMask'] = DB::Aowow()->selectCell('SELECT BIT_OR(equippedItemSubClassMask) FROM dbc_spell s JOIN dbc_skilllineability sla ON sla.spellId = s.id JOIN dbc_skillraceclassinfo srci ON srci.skillLine = sla.skillLineId AND srci.classMask & ?d WHERE sla.skilllineid <> 183 AND (sla.reqClassMask & ?d OR sla.reqClassMask = 0) AND equippedItemClass = ?d AND (effect1Id = 60 OR effect2Id = 60)', 1 << ($id - 1), 1 << ($id - 1), ITEM_CLASS_WEAPON);
            $data['armorTypeMask']  = DB::Aowow()->selectCell('SELECT BIT_OR(equippedItemSubClassMask) FROM dbc_spell s JOIN dbc_skilllineability sla ON sla.spellId = s.id JOIN dbc_skillraceclassinfo srci ON srci.skillLine = sla.skillLineId AND srci.classMask & ?d WHERE                             sla.reqClassMask & ?d                          AND equippedItemClass = ?d AND (effect1Id = 60 OR effect2Id = 60)', 1 << ($id - 1), 1 << ($id - 1), ITEM_CLASS_ARMOR);
        }

        foreach ($classes as $cl)
            DB::Aowow()->query('REPLACE INTO ?_classes (?#) VALUES (?a)', array_keys($cl), array_values($cl));

        $this->reapplyCCFlags('classes', Type::CHR_CLASS);

        return true;
    }
});

?>
