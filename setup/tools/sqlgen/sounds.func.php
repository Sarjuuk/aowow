<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


$customData = array(
    15407 => ['cat' => 10]                                  // UR_Algalon_Summon03 (this is not an item pickup)
);
$reqDBC = ['soundentries', 'emotestextsound', 'npcsounds', 'creaturesounddata', 'creaturedisplayinfo', 'creaturemodeldata', 'vocaluisounds'];

function sounds(/*array $ids = [] */)
{
    /***********/
    /* M A I N */
    /***********/

    CLISetup::log(' - sounds main data');

    // file extraction and conversion in build step. data here is purely structural
    // reality check ... thats probably gigabytes worth of sound.. only growing in size with every locale added on top (RedRocketSite didn't do it. Should i then?)

    // .wav => audio/ogg; codecs="vorbis"
    // .mp3 => audio/mpeg

    $query = '
        SELECT Id AS `id`, `type` AS `cat`, `name`, 0 AS cuFlags,
            `file1` AS soundFile1, `file2` AS soundFile2, `file3` AS soundFile3, `file4` AS soundFile4, `file5` AS soundFile5,
            `file6` AS soundFile6, `file7` AS soundFile7, `file8` AS soundFile8, `file9` AS soundFile9, `file10` AS soundFile10,
            path, flags
        FROM dbc_soundentries
        WHERE id > ?d LIMIT ?d
    ';

    DB::Aowow()->query('TRUNCATE ?_sounds');
    DB::Aowow()->query('TRUNCATE ?_sounds_files');

    $lastMax      = 0;
    $soundFileIdx = 0;
    $soundIndex   = [];
    while ($sounds = DB::Aowow()->select($query, $lastMax, SqlGen::$stepSize))
    {
        $newMax = max(array_column($sounds, 'id'));

        CLISetup::log('   * sets '.($lastMax + 1).' - '.$newMax);

        $lastMax = $newMax;

        $groupSets = [];
        foreach ($sounds as $s)
        {
            /* attention!

                one sound can be used in 20 or more locations but may appear as multiple files,
                because of different cases, path being attached to file and other shenanigans

                build a usable path and create full index to compensate
                25.6k raw files => expect ~21k filtered files
            */

            $fileSets = [];
            $hasDupes = false;
            for ($i = 1; $i < 11; $i++)
            {
                $nicePath = CLISetup::nicePath($s['soundFile'.$i], $s['path']);
                if ($s['soundFile'.$i] && array_key_exists($nicePath, $soundIndex))
                {
                    $s['soundFile'.$i] = $soundIndex[$nicePath];
                    $hasDupes = true;
                    continue;
                }

                // convert to something web friendly => ogg
                if (stristr($s['soundFile'.$i], '.wav'))
                {
                    $soundIndex[$nicePath] = ++$soundFileIdx;

                    $fileSets[] = array(
                        $soundFileIdx,
                        strtolower($s['soundFile'.$i]),
                        strtolower($s['path']),
                        SOUND_TYPE_OGG
                    );
                    $s['soundFile'.$i] = $soundFileIdx;
                }
                // mp3 .. keep as is
                else if (stristr($s['soundFile'.$i], '.mp3'))
                {
                    $soundIndex[$nicePath] = ++$soundFileIdx;

                    $fileSets[] = array(
                        $soundFileIdx,
                        strtolower($s['soundFile'.$i]),
                        strtolower($s['path']),
                        SOUND_TYPE_MP3
                    );
                    $s['soundFile'.$i] = $soundFileIdx;
                }
                // i call bullshit
                else if ($s['soundFile'.$i])
                {
                    CLISetup::log('   - sound group #'.$s['id'].' "'.$s['name'].'" has invalid sound file "'.$s['soundFile'.$i].'" on index '.$i.'! Skipping...', CLISetup::LOG_WARN);
                    $s['soundFile'.$i] = null;
                }
                // empty case
                else
                    $s['soundFile'.$i] = null;
            }

            if (!$fileSets && !$hasDupes)
            {
                CLISetup::log('   - sound group #'.$s['id'].' "'.$s['name'].'" contains no sound files! Skipping...', CLISetup::LOG_WARN);
                continue;
            }
            else if ($fileSets)
                DB::Aowow()->query('INSERT INTO ?_sounds_files VALUES (?a)', array_values($fileSets));

            unset($s['path']);

            $groupSets[] = array_values($s);
        }

        DB::Aowow()->query('REPLACE INTO ?_sounds VALUES (?a)', array_values($groupSets));
    }


    /******************/
    /* VocalUI Sounds */
    /******************/

    CLISetup::log(' - linking to race');

    DB::Aowow()->query('TRUNCATE ?_races_sounds');
    DB::Aowow()->query('INSERT IGNORE INTO ?_races_sounds SELECT raceId, soundIdMale,   1 FROM dbc_vocaluisounds WHERE soundIdMale <> soundIdFemale AND soundIdMale   > 0');
    DB::Aowow()->query('INSERT IGNORE INTO ?_races_sounds SELECT raceId, soundIdFemale, 2 FROM dbc_vocaluisounds WHERE soundIdMale <> soundIdFemale AND soundIdFemale > 0');

    // ps: im too dumb to union this


    /***************/
    /* Emote Sound */
    /***************/

    CLISetup::log(' - linking to emotes');

    DB::Aowow()->query('TRUNCATE ?_emotes_sounds');
    DB::Aowow()->query('INSERT IGNORE INTO ?_emotes_sounds SELECT emotesTextId, raceId, gender + 1, soundId FROM dbc_emotestextsound');


    /*******************/
    /* Creature Sounds */
    /*******************/

    // currently ommitting:
    //      * footsteps (matrix of: creature + terrain + humidity)
    //      * fidget2 through 5
    //      * customattack2 through 3
    //  in case of conficting data CreatureDisplayInfo overrides CreatureModelData (seems to be more specialized (Thral > MaleOrc / Maiden > FemaleTitan))

    CLISetup::log(' - linking to creatures');

    DB::Aowow()->query('TRUNCATE ?_creature_sounds');
    DB::Aowow()->query('
        INSERT INTO
            ?_creature_sounds (`id`, `greeting`, `farewell`, `angry`, `exertion`, `exertioncritical`, `injury`, `injurycritical`, `death`, `stun`, `stand`, `aggro`, `wingflap`, `wingglide`, `alert`, `fidget`, `customattack`, `loop`, `jumpstart`, `jumpend`, `petattack`, `petorder`, `petdismiss`, `birth`, `spellcast`, `submerge`, `submerged`)
        SELECT
            cdi.Id,
            IFNULL(ns.greetSoundId, 0),
            IFNULL(ns.byeSoundId,   0),
            IFNULL(ns.angrySoundId, 0),
            IF(csdA.exertion,         csdA.exertion,         IFNULL(csdB.exertion,         0)),
            IF(csdA.exertionCritical, csdA.exertionCritical, IFNULL(csdB.exertionCritical, 0)),
            IF(csdA.injury,           csdA.injury,           IFNULL(csdB.injury,           0)),
            IF(csdA.injuryCritical,   csdA.injuryCritical,   IFNULL(csdB.injuryCritical,   0)),
            IF(csdA.death,            csdA.death,            IFNULL(csdB.death,            0)),
            IF(csdA.stun,             csdA.stun,             IFNULL(csdB.stun,             0)),
            IF(csdA.stand,            csdA.stand,            IFNULL(csdB.stand,            0)),
            IF(csdA.aggro,            csdA.aggro,            IFNULL(csdB.aggro,            0)),
            IF(csdA.wingFlap,         csdA.wingFlap,         IFNULL(csdB.wingFlap,         0)),
            IF(csdA.wingGlide,        csdA.wingGlide,        IFNULL(csdB.wingGlide,        0)),
            IF(csdA.alert,            csdA.alert,            IFNULL(csdB.alert,            0)),
            IF(csdA.fidget,           csdA.fidget,           IFNULL(csdB.fidget,           0)),
            IF(csdA.customAttack,     csdA.customAttack,     IFNULL(csdB.customAttack,     0)),
            IF(csdA.loop,             csdA.loop,             IFNULL(csdB.loop,             0)),
            IF(csdA.jumpStart,        csdA.jumpStart,        IFNULL(csdB.jumpStart,        0)),
            IF(csdA.jumpEnd,          csdA.jumpEnd,          IFNULL(csdB.jumpEnd,          0)),
            IF(csdA.petAttack,        csdA.petAttack,        IFNULL(csdB.petAttack,        0)),
            IF(csdA.petOrder,         csdA.petOrder,         IFNULL(csdB.petOrder,         0)),
            IF(csdA.petDismiss,       csdA.petDismiss,       IFNULL(csdB.petDismiss,       0)),
            IF(csdA.birth,            csdA.birth,            IFNULL(csdB.birth,            0)),
            IF(csdA.spellcast,        csdA.spellcast,        IFNULL(csdB.spellcast,        0)),
            IF(csdA.submerge,         csdA.submerge,         IFNULL(csdB.submerge,         0)),
            IF(csdA.submerged,        csdA.submerged,        IFNULL(csdB.submerged,        0))
        FROM
            dbc_creaturedisplayinfo cdi
        LEFT JOIN
            dbc_creaturemodeldata cmd ON cmd.Id = cdi.modelId
        LEFT JOIN
            dbc_creaturesounddata csdA ON cdi.creatureSoundId = csdA.Id
        LEFT JOIN
            dbc_creaturesounddata csdB ON cmd.creatureSoundId = csdB.Id
        LEFT JOIN
            dbc_npcsounds ns ON cdi.npcSoundId = ns.Id
    ');


    return true;
}

?>
