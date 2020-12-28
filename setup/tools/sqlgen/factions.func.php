<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;

    protected $command = 'factions';

    protected $dbcSourceFiles = ['faction', 'factiontemplate'];

    private $customData = array(
          47 => ['qmNpcIds' => '33310'],
          68 => ['qmNpcIds' => '33555'],
          69 => ['qmNpcIds' => '33653'],
          72 => ['qmNpcIds' => '33307'],
          76 => ['qmNpcIds' => '33553'],
          81 => ['qmNpcIds' => '33556'],
         922 => ['qmNpcIds' => '16528'],
         930 => ['qmNpcIds' => '33657'],
         932 => ['qmNpcIds' => '19321'],
         933 => ['qmNpcIds' => '20242 23007'],
         935 => ['qmNpcIds' => '21432'],
         941 => ['qmNpcIds' => '20241'],
         942 => ['qmNpcIds' => '17904'],
         946 => ['qmNpcIds' => '17657'],
         947 => ['qmNpcIds' => '17585'],
         970 => ['qmNpcIds' => '18382'],
         978 => ['qmNpcIds' => '20240'],
         989 => ['qmNpcIds' => '21643'],
        1011 => ['qmNpcIds' => '21655'],
        1012 => ['qmNpcIds' => '23159'],
        1037 => ['qmNpcIds' => '32773 32564'],
        1038 => ['qmNpcIds' => '23428'],
        1052 => ['qmNpcIds' => '32774 32565'],
        1073 => ['qmNpcIds' => '31916 32763'],
        1090 => ['qmNpcIds' => '32287'],
        1091 => ['qmNpcIds' => '32533'],
        1094 => ['qmNpcIds' => '34881'],
        1105 => ['qmNpcIds' => '31910'],
        1106 => ['qmNpcIds' => '30431'],
        1119 => ['qmNpcIds' => '32540'],
        1124 => ['qmNpcIds' => '34772'],
        1156 => ['qmNpcIds' => '37687'],
        1082 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
         952 => ['cuFlags' => CUSTOM_EXCLUDE_FOR_LISTVIEW],
    );

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

        return true;
    }
});

?>
