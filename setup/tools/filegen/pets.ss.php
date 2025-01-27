<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


/* Example data
    30: {
        id:30,
        name:'Forest Spider',
        minlevel:5,
        maxlevel:6,
        location:[12],                                  // master-AreaTableId's (?)
        react:[-1,-1],
        classification:0,                               // 0:"Normal", 1:"Elite", 2:"Rar Elite", 3:"Boss", 4:"Rar"
        family:3,                                       // creatureFamily
        displayId:382,
        skin:'TarantulaSkinOrange',
        icon:'Ability_Hunter_Pet_Spider',               // from creatureFamily.dbc
        type:2                                          // 0:Ferocity, 1:Tenacity, 2:Cunning
    },
*/

// builds 'pets'-file for available locales
CLISetup::registerSetup("build", new class extends SetupScript
{
    protected $info = array(
        'pets' => [[], CLISetup::ARGV_PARAM, 'Compiles tameable hunter pets to file for the talent calculator tool.']
    );

    protected $dbcSourceFiles = ['creaturefamily'];
    protected $setupAfter     = [['creature', 'factions', 'spawns'], []];
    protected $requiredDirs   = ['datasets/'];
    protected $localized      = true;

    public function generate() : bool
    {
        $petList   = DB::Aowow()->Select(
           'SELECT   cr.`id`,
                     cr.`name_loc0`, cr.`name_loc2`, cr.`name_loc3`, cr.`name_loc4`, cr.`name_loc6`, cr.`name_loc8`,
                     cr.`minLevel`, cr.`maxLevel`,
                     ft.`A`, ft.`H`,
                     cr.`rank` AS "classification",
                     cr.`family`,
                     cr.`displayId1` AS "displayId",
                     cr.`textureString` AS "skin",
                     LOWER(SUBSTRING_INDEX(cf.`iconString`, "\\\\", -1)) AS "icon",
                     cf.`petTalentType` AS "type"
            FROM     ?_creature cr
            JOIN     ?_factiontemplate  ft ON ft.`id` = cr.`faction`
            JOIN     dbc_creaturefamily cf ON cf.`id` = cr.`family`
            WHERE    cr.`typeFlags` & 0x1 AND (cr.`cuFlags` & ?d) = 0
            ORDER BY cr.`id` ASC',
            NPC_CU_DIFFICULTY_DUMMY
        );

        $locations = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, `areaId` AS ARRAY_KEY2, `areaId` FROM ?_spawns WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId`, `areaId`', Type::NPC, array_column($petList, 'id'));

        foreach (CLISetup::$locales as $loc)
        {
            Lang::load($loc);

            $petsOut = [];
            foreach ($petList as $pet)
            {
                $petsOut[$pet['id']] = array(
                    'id'             => $pet['id'],
                    'name'           => Util::localizedString($pet, 'name'),
                    'minlevel'       => $pet['minLevel'],
                    'maxlevel'       => $pet['maxLevel'],
                    'location'       => $locations[$pet['id']] ?? [],
                    'react'          => [$pet['A'], $pet['H']],
                    'classification' => $pet['classification'],
                    'family'         => $pet['family'],
                    'displayId'      => $pet['displayId'],
                    'skin'           => $pet['skin'],
                    'icon'           => $pet['icon'],
                    'type'           => $pet['type']
                );
            }

            $toFile = "var g_pets = ".Util::toJSON($petsOut).";";
            $file   = 'datasets/'.$loc->json().'/pets';

            if (!CLISetup::writeFile($file, $toFile))
                $this->success = false;
        }

        return $this->success;
    }
});

?>
