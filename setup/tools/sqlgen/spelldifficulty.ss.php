<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


CLISetup::registerSetup("sql", new class extends SetupScript
{
    protected $info = array(
        'spelldifficulty' => [[], CLISetup::ARGV_PARAM, 'Compiles supplemental data for type: Spell from dbc and world db.']
    );

    protected $dbcSourceFiles  = ['spelldifficulty'];
    protected $worldDependency = ['spelldifficulty_dbc'];
    protected $setupAfter      = [['creature', 'spawns'], []];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE TABLE ::spelldifficulty');

        DB::Aowow()->qry('INSERT INTO ::spelldifficulty SELECT GREATEST(`normal10`, 0), GREATEST(`normal25`, 0), GREATEST(`heroic10`, 0), GREATEST(`heroic25`, 0), IF(`heroic10` > 0, 2, 0) FROM dbc_spelldifficulty');

        $rows = DB::World()->selectAssoc('SELECT `spellid0`, `spellid1`, `spellid2`, `spellid3`, IF(`spellid2` > 0, 2, 0) FROM spelldifficulty_dbc');
        foreach ($rows as $r)
            DB::Aowow()->qry('INSERT INTO ::spelldifficulty VALUES %l', $r);


        CLI::write('[spelldifficulty] - trying to assign map type by traversing creature spells > spawns');

        // try to update mode of ambiguous entries
        $baseSpells = DB::Aowow()->selectCol('SELECT `normal10` FROM ::spelldifficulty WHERE `heroic10` = 0 AND `heroic25` = 0');

        for ($i = 1; $i < 9; $i++)
            DB::Aowow()->qry(
               'UPDATE ::spelldifficulty sd,
                       (SELECT   c.%n AS "spell", BIT_OR(CASE WHEN z.`type` = %i THEN 1 WHEN z.`type` = %i THEN 2 WHEN z.`type` = %i THEN 2 ELSE 0 END) AS "mapType"
                        FROM     ::creature c
                        JOIN     ::spawns s ON c.id = s.typeId AND s.type = %i
                        JOIN     ::zones z ON z.id = s.areaId
                        WHERE    c.%n IN %in
                        GROUP BY c.%n
                        HAVING   c.%n <> 0) x
                SET     sd.`mapType` = x.`mapType`
                WHERE   sd.`normal10` = x.`spell`',
                'spell'.$i, MAP_TYPE_DUNGEON_HC, MAP_TYPE_MMODE_RAID, MAP_TYPE_MMODE_RAID_HC,
                Type::NPC, 'spell'.$i, $baseSpells, 'spell'.$i, 'spell'.$i
            );


        CLI::write('[spelldifficulty] - trying to assign map type by traversing smart_scripts > spawns');

        $smartCaster = [];
        foreach ($baseSpells as $bs)
            if ($owner = SmartAI::getOwnerOfSpellCast($bs))
                foreach ($owner as $type => $caster)
                    $smartCaster[$type][$bs] = $caster;

        foreach ($smartCaster as $type => $spells)
            foreach ($spells as $spellId => $casterEntries)
                DB::Aowow()->qry(
                   'UPDATE ::spelldifficulty sd,
                           (SELECT BIT_OR(CASE WHEN z.`type` = %i THEN 1 WHEN z.`type` = %i THEN 2 WHEN z.`type` = %i THEN 2 ELSE 0 END) AS "mapType"
                            FROM   ::spawns s
                            JOIN   ::zones z ON z.id = s.areaId
                            WHERE s.type = %i AND s.typeId IN %in ) sp
                    SET    sd.`mapType` = IF(sp.`mapType` > 2, 0, sp.`mapType`)
                    WHERE sd.`normal10` = %i',
                    MAP_TYPE_DUNGEON_HC, MAP_TYPE_MMODE_RAID, MAP_TYPE_MMODE_RAID_HC,
                    $type, $casterEntries,
                    $spellId
                );

        return true;
    }
});

?>
