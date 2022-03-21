<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'factions';

    protected $dbcSourceFiles = ['faction', 'factiontemplate'];

    public function generate(array $ids = []) : bool
    {
        $factionQuery = '
            REPLACE INTO
                ?_factions
            SELECT
                f.id,
                f.repIdx,
                baseRepRaceMask1,  baseRepRaceMask2,  baseRepRaceMask3,  baseRepRaceMask4,
                baseRepClassMask1, baseRepClassMask2, baseRepClassMask3, baseRepClassMask4,
                baseRepValue1,     baseRepValue2,     baseRepValue3,     baseRepValue4,
                IF(SUM(ft.ourMask & 0x6) / COUNT(1) = 0x4, 2, IF(SUM(ft.ourMask & 0x6) / COUNT(1) = 0x2, 1, 0)) as side,
                0,                                                  -- expansion
                "",                                                 -- quartermasterNpcIds
                "",                                                 -- factionTemplateIds
                0,                                                  -- cuFlags
                parentFaction,
                spilloverRateIn, spilloverRateOut, spilloverMaxRank,
                name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8
            FROM
                dbc_faction f
            LEFT JOIN
                dbc_factiontemplate ft ON ft.factionid = f.id
            GROUP BY
                f.id';

        $templateQuery = '
            UPDATE
                ?_factions f
            JOIN
                (SELECT ft.factionId, GROUP_CONCAT(ft.id SEPARATOR " ") AS tplIds FROM dbc_factiontemplate ft GROUP BY ft.factionId) temp ON f.id = temp.factionId
            SET
                f.templateIds = temp.tplIds';

        $recursiveUpdateQuery = '
            UPDATE
                ?_factions top
            JOIN
                (SELECT id, parentFactionId FROM ?_factions) mid ON mid.parentFactionId IN (?a)
            LEFT JOIN
                (SELECT id, parentFactionId FROM ?_factions) low ON low.parentFactionId = mid.id
            SET
                ?a
            WHERE
                repIdx > 0 AND (
                    top.id IN (?a) OR
                    top.id = mid.id OR
                    top.id = low.id
                )';

        $excludeQuery = '
            UPDATE
                ?_factions x
            JOIN
                dbc_faction f ON f.id = x.id
            LEFT JOIN
                dbc_factiontemplate ft ON f.id = ft.factionId
            SET
                cuFlags = cuFlags | ?d
            WHERE
                f.repIdx < 0 OR
                (
                    f.repIdx > 0 AND
                    (f.repFlags1 & 0x8 OR ft.id IS NULL) AND
                    (f.repFlags1 & 0x80) = 0
                )';

        $pairs = array(
            [[980],  ['expansion' => 1]],
            [[1097], ['expansion' => 2]],
            [[469, 891, 1037], ['side' => 1]],
            [[ 67, 892, 1052], ['side' => 2]],
        );

        DB::Aowow()->query($factionQuery);
        DB::Aowow()->query($templateQuery);
        DB::Aowow()->query($excludeQuery, CUSTOM_EXCLUDE_FOR_LISTVIEW);

        foreach ($pairs as $p)
            DB::Aowow()->query($recursiveUpdateQuery, $p[0], $p[1], $p[0]);

        $this->reapplyCCFlags('factions', Type::FACTION);

        return true;
    }
});

?>
