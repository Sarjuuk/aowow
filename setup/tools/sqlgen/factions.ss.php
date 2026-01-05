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
        'factions' => [[], CLISetup::ARGV_PARAM, 'Compiles data for type: Faction from dbc.']
    );

    protected $dbcSourceFiles = ['faction', 'factiontemplate'];

    public function generate() : bool
    {
        DB::Aowow()->qry('TRUNCATE ::factions');
        DB::Aowow()->qry('TRUNCATE ::factiontemplate');

        DB::Aowow()->qry(
           'INSERT INTO ::factions
            SELECT      f.`id`,
                        f.`repIdx`,
                        `baseRepRaceMask1`,  `baseRepRaceMask2`,  `baseRepRaceMask3`,  `baseRepRaceMask4`,
                        `baseRepClassMask1`, `baseRepClassMask2`, `baseRepClassMask3`, `baseRepClassMask4`,
                        `baseRepValue1`,     `baseRepValue2`,     `baseRepValue3`,     `baseRepValue4`,
                        IF(SUM(ft.`ourMask` & 0x6) / COUNT(1) = 0x4, 2, IF(SUM(ft.`ourMask` & 0x6) / COUNT(1) = 0x2, 1, 0)) AS "side",
                        0,                                 -- expansion
                        "",                                -- quartermasterNpcIds
                        "",                                -- factionTemplateIds
                        0,                                 -- cuFlags
                        `parentFaction`,
                        `spilloverRateIn`, `spilloverRateOut`, `spilloverMaxRank`,
                        `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`
            FROM        dbc_faction f
            LEFT JOIN   dbc_factiontemplate ft ON ft.`factionid` = f.`id`
            GROUP BY    f.`id`'
        );

        DB::Aowow()->qry(
           'INSERT INTO ::factiontemplate
            SELECT      `id`,
                        `factionId`,
                        IF(`friendFactionId1` = 1 OR `friendFactionId2` = 1 OR `friendFactionId3` = 1 OR `friendFactionId4` = 1 OR `friendlyMask` & 0x3,  1,
                        IF(`enemyFactionId1`  = 1 OR `enemyFactionId2`  = 1 OR `enemyFactionId3`  = 1 OR `enemyFactionId4`  = 1 OR `hostileMask`  & 0x3, -1, 0)),
                        IF(`friendFactionId1` = 2 OR `friendFactionId2` = 2 OR `friendFactionId3` = 2 OR `friendFactionId4` = 2 OR `friendlyMask` & 0x5,  1,
                        IF(`enemyFactionId1`  = 2 OR `enemyFactionId2`  = 2 OR `enemyFactionId3`  = 2 OR `enemyFactionId4`  = 2 OR `hostileMask`  & 0x5, -1, 0))
            FROM        dbc_factiontemplate'
        );

        DB::Aowow()->qry(
           'UPDATE ::factions f
            JOIN   (SELECT ft.`factionId`, GROUP_CONCAT(ft.`id` SEPARATOR " ") AS "tplIds" FROM dbc_factiontemplate ft GROUP BY ft.`factionId`) temp ON f.`id` = temp.`factionId`
            SET    f.`templateIds` = temp.`tplIds`'
        );

        DB::Aowow()->qry(
           'UPDATE    ::factions x
            JOIN      dbc_faction f ON f.`id` = x.`id`
            SET       `cuFlags` = `cuFlags` | %i
            WHERE     f.`repIdx` < 0 OR f.`id` = 952 OR ( (f.`repFlags1` & %i) > 0 AND f.`id` NOT IN (67, 169, 469, 589, 1085) AND (f.`repFLags1` & %i) = 0 )',
            CUSTOM_EXCLUDE_FOR_LISTVIEW,
            FACTION_FLAG_HIDDEN | FACTION_FLAG_INVISIBLE_FORCED, FACTION_FLAG_SPECIAL
        );

        $pairs = array(
            [[980],  ['expansion' => EXP_BC]],
            [[1097], ['expansion' => EXP_WOTLK]],
            [[469, 891, 1037], ['side' => SIDE_ALLIANCE]],
            [[ 67, 892, 1052], ['side' => SIDE_HORDE]],
        );

        foreach ($pairs as [$factions, $update])
            DB::Aowow()->qry(
                'UPDATE    ::factions top
                 JOIN      (SELECT `id`, `parentFactionId` FROM ::factions) mid ON mid.`parentFactionId` IN %in
                 LEFT JOIN (SELECT `id`, `parentFactionId` FROM ::factions) low ON low.`parentFactionId` = mid.`id`
                 SET       %a
                 WHERE     `repIdx` > 0 AND (top.`id` IN %in OR top.`id` = mid.`id` OR top.`id` = low.`id`)',
                 $factions, $update, $factions
            );

        $this->reapplyCCFlags('factions', Type::FACTION);

        return true;
    }
});

?>
