<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

if (!CLI)
    die('not in cli mode');


SqlGen::register(new class extends SetupScript
{
    use TrCustomData;                                       // import custom data from DB

    protected $command = 'sounds';

    protected $dbcSourceFiles = array(
        // base          emotes             race
        'soundentries', 'emotestextsound', 'vocaluisounds',
        // creatures
        'npcsounds', 'creaturesounddata', 'creaturedisplayinfo', 'creaturemodeldata',
        // spells
        'spell', 'spellvisual', 'spellvisualkit', 'screeneffect',
        // zones
        'soundambience', 'zonemusic', 'zoneintromusictable', 'worldstatezonesounds', 'areatable',
        // items
        'material', 'itemgroupsounds', 'itemdisplayinfo', 'weaponimpactsounds', 'itemsubclass', 'weaponswingsounds2' /*, 'sheathesoundlookups' data is redundant with material..? */
    );

    public function generate(array $ids = []) : bool
    {
        /*
            okay, here's the thing. WMOAreaTable.dbc references WMO-files to get its position in the world (AreTable) and has sparse information on the related AreaTables themself.
            Though it has sets for ZoneAmbience, ZoneMusic and ZoneIntroMusic, these can't be linked for this very reason and are omitted for now.
            content: e.g. Tavern Music
        */

        // WMOAreaTable.dbc/id => AreaTable.dbc/id
        $worldStateZoneSoundFix = array(
            18153 => 2119,
            18154 => 2119,
            47321 => 4273,                                  // The Spark of Imagination
            43600 => 4273,                                  // The Celestial Planetarium
            47478 => 4273                                   // The Prison of Yogg-Saron
        );


        /***********/
        /* M A I N */
        /***********/

        CLI::write(' - sounds main data');

        // file extraction and conversion manually
        // moving files in build step. data here is purely structural
        // reality check ... thats probably gigabytes worth of sound.. only growing in size with every locale added on top (RedRocketSite didn't do it. Should i then?)

        // .wav => audio/ogg; codecs="vorbis"
        // .mp3 => audio/mpeg

        $query = '
            SELECT id AS `id`, `type` AS `cat`, `name`, 0 AS cuFlags,
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
        $j = 0;
        while ($sounds = DB::Aowow()->select($query, $lastMax, SqlGen::$sqlBatchSize))
        {
            $newMax = max(array_column($sounds, 'id'));

            CLI::write('   * batch #' . ++$j . ' (' . count($sounds) . ')', CLI::LOG_BLANK, true, true);

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
                    $nicePath = CLI::nicePath($s['soundFile'.$i], $s['path']);
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
                            $s['soundFile'.$i],
                            $s['path'],
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
                            $s['soundFile'.$i],
                            $s['path'],
                            SOUND_TYPE_MP3
                        );
                        $s['soundFile'.$i] = $soundFileIdx;
                    }
                    // i call bullshit
                    else if ($s['soundFile'.$i])
                    {
                        CLI::write('   - sound group #'.$s['id'].' "'.$s['name'].'" has invalid sound file "'.$s['soundFile'.$i].'" on index '.$i.'! Skipping...', CLI::LOG_WARN);
                        $s['soundFile'.$i] = null;
                    }
                    // empty case
                    else
                        $s['soundFile'.$i] = null;
                }

                if (!$fileSets && !$hasDupes)
                {
                    CLI::write('   - sound group #'.$s['id'].' "'.$s['name'].'" contains no sound files! Skipping...', CLI::LOG_WARN);
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

        CLI::write(' - linking to race');

        DB::Aowow()->query('TRUNCATE ?_races_sounds');                                                                                                                        // just to silence expected duplicate key errors
        DB::Aowow()->query('INSERT INTO ?_races_sounds SELECT `raceId`, `soundIdMale`,   1 FROM dbc_vocaluisounds WHERE `soundIdMale` <> `soundIdFemale` AND `soundIdMale`   > 0 ON DUPLICATE KEY UPDATE `soundId` = `soundId`');
        DB::Aowow()->query('INSERT INTO ?_races_sounds SELECT `raceId`, `soundIdFemale`, 2 FROM dbc_vocaluisounds WHERE `soundIdMale` <> `soundIdFemale` AND `soundIdFemale` > 0 ON DUPLICATE KEY UPDATE `soundId` = `soundId`');

        // ps: im too dumb to union this


        /***************/
        /* Emote Sound */
        /***************/

        CLI::write(' - linking to emotes');

        DB::Aowow()->query('TRUNCATE ?_emotes_sounds');                                                                                // just to silence expected duplicate key errors
        DB::Aowow()->query('INSERT INTO ?_emotes_sounds SELECT `emotesTextId`, `raceId`, `gender` + 1, `soundId` FROM dbc_emotestextsound ON DUPLICATE KEY UPDATE `emoteId` = `emoteId`');


        /*******************/
        /* Creature Sounds */
        /*******************/

        CLI::write(' - linking to creatures');

        // currently ommitting:
        //      * footsteps (matrix of: creature + terrain + humidity)
        //      * fidget2 through 5
        //      * customattack2 through 3
        //  in case of conflicting data CreatureDisplayInfo overrides CreatureModelData (seems to be more specialized (Thral > MaleOrc / Maiden > FemaleTitan))

        DB::Aowow()->query('TRUNCATE ?_creature_sounds');
        DB::Aowow()->query('
            INSERT INTO
                ?_creature_sounds (`id`, `greeting`, `farewell`, `angry`, `exertion`, `exertioncritical`, `injury`, `injurycritical`, `death`, `stun`, `stand`, `aggro`, `wingflap`, `wingglide`, `alert`, `fidget`, `customattack`, `loop`, `jumpstart`, `jumpend`, `petattack`, `petorder`, `petdismiss`, `birth`, `spellcast`, `submerge`, `submerged`)
            SELECT
                cdi.id,
                GREATEST(IFNULL(ns.greetSoundId, 0), 0),
                GREATEST(IFNULL(ns.byeSoundId,   0), 0),
                GREATEST(IFNULL(ns.angrySoundId, 0), 0),
                GREATEST(IF(csdA.exertion,         csdA.exertion,         IFNULL(csdB.exertion,         0)), 0),
                GREATEST(IF(csdA.exertionCritical, csdA.exertionCritical, IFNULL(csdB.exertionCritical, 0)), 0),
                GREATEST(IF(csdA.injury,           csdA.injury,           IFNULL(csdB.injury,           0)), 0),
                GREATEST(IF(csdA.injuryCritical,   csdA.injuryCritical,   IFNULL(csdB.injuryCritical,   0)), 0),
                GREATEST(IF(csdA.death,            csdA.death,            IFNULL(csdB.death,            0)), 0),
                GREATEST(IF(csdA.stun,             csdA.stun,             IFNULL(csdB.stun,             0)), 0),
                GREATEST(IF(csdA.stand,            csdA.stand,            IFNULL(csdB.stand,            0)), 0),
                GREATEST(IF(csdA.aggro,            csdA.aggro,            IFNULL(csdB.aggro,            0)), 0),
                GREATEST(IF(csdA.wingFlap,         csdA.wingFlap,         IFNULL(csdB.wingFlap,         0)), 0),
                GREATEST(IF(csdA.wingGlide,        csdA.wingGlide,        IFNULL(csdB.wingGlide,        0)), 0),
                GREATEST(IF(csdA.alert,            csdA.alert,            IFNULL(csdB.alert,            0)), 0),
                GREATEST(IF(csdA.fidget,           csdA.fidget,           IFNULL(csdB.fidget,           0)), 0),
                GREATEST(IF(csdA.customAttack,     csdA.customAttack,     IFNULL(csdB.customAttack,     0)), 0),
                GREATEST(IF(csdA.loop,             csdA.loop,             IFNULL(csdB.loop,             0)), 0),
                GREATEST(IF(csdA.jumpStart,        csdA.jumpStart,        IFNULL(csdB.jumpStart,        0)), 0),
                GREATEST(IF(csdA.jumpEnd,          csdA.jumpEnd,          IFNULL(csdB.jumpEnd,          0)), 0),
                GREATEST(IF(csdA.petAttack,        csdA.petAttack,        IFNULL(csdB.petAttack,        0)), 0),
                GREATEST(IF(csdA.petOrder,         csdA.petOrder,         IFNULL(csdB.petOrder,         0)), 0),
                GREATEST(IF(csdA.petDismiss,       csdA.petDismiss,       IFNULL(csdB.petDismiss,       0)), 0),
                GREATEST(IF(csdA.birth,            csdA.birth,            IFNULL(csdB.birth,            0)), 0),
                GREATEST(IF(csdA.spellcast,        csdA.spellcast,        IFNULL(csdB.spellcast,        0)), 0),
                GREATEST(IF(csdA.submerge,         csdA.submerge,         IFNULL(csdB.submerge,         0)), 0),
                GREATEST(IF(csdA.submerged,        csdA.submerged,        IFNULL(csdB.submerged,        0)), 0)
            FROM
                dbc_creaturedisplayinfo cdi
            LEFT JOIN
                dbc_creaturemodeldata cmd ON cmd.id = cdi.modelId
            LEFT JOIN
                dbc_creaturesounddata csdA ON cdi.creatureSoundId = csdA.id
            LEFT JOIN
                dbc_creaturesounddata csdB ON cmd.creatureSoundId = csdB.id
            LEFT JOIN
                dbc_npcsounds ns ON cdi.npcSoundId = ns.id
        ');


        /****************/
        /* Spell Sounds */
        /****************/

        CLI::write(' - linking to spells');

        // issues: (probably because of 335-data)
        //      * animate is probably wrong
        //      * missile and impactarea not in js
        //      * ready, castertargeting, casterstate and targetstate not in dbc

        DB::Aowow()->query('TRUNCATE ?_spell_sounds');
        DB::Aowow()->query('
            INSERT INTO
                ?_spell_sounds (`id`, `precast`, `cast`, `impact`, `state`, `statedone`, `channel`, `missile`, `animation`, `casterimpact`, `targetimpact`, `missiletargeting`, `instantarea`, `impactarea`, `persistentarea`)
            SELECT
                sv.id,
                GREATEST(IFNULL(svk1.soundId, 0), 0),
                GREATEST(IFNULL(svk2.soundId, 0), 0),
                GREATEST(IFNULL(svk3.soundId, 0), 0),
                GREATEST(IFNULL(svk4.soundId, 0), 0),
                GREATEST(IFNULL(svk5.soundId, 0), 0),
                GREATEST(IFNULL(svk6.soundId, 0), 0),
                GREATEST(missileSoundId, 0),
                GREATEST(animationSoundId, 0),
                GREATEST(IFNULL(svk7.soundId, 0), 0),
                GREATEST(IFNULL(svk8.soundId, 0), 0),
                GREATEST(IFNULL(svk9.soundId, 0), 0),
                GREATEST(IFNULL(svk10.soundId, 0), 0),
                GREATEST(IFNULL(svk11.soundId, 0), 0),
                GREATEST(IFNULL(svk12.soundId, 0), 0)
            FROM
                dbc_spellvisual sv
            LEFT JOIN
                dbc_spellvisualkit svk1  ON svk1.id  = sv.precastKitId
            LEFT JOIN
                dbc_spellvisualkit svk2  ON svk2.id  = sv.castKitId
            LEFT JOIN
                dbc_spellvisualkit svk3  ON svk3.id  = sv.impactKitId
            LEFT JOIN
                dbc_spellvisualkit svk4  ON svk4.id  = sv.stateKitId
            LEFT JOIN
                dbc_spellvisualkit svk5  ON svk5.id  = sv.statedoneKitId
            LEFT JOIN
                dbc_spellvisualkit svk6  ON svk6.id  = sv.channelKitId
            LEFT JOIN
                dbc_spellvisualkit svk7  ON svk7.id  = sv.casterImpactKitId
            LEFT JOIN
                dbc_spellvisualkit svk8  ON svk8.id  = sv.targetImpactKitId
            LEFT JOIN
                dbc_spellvisualkit svk9  ON svk9.id  = sv.missileTargetingKitId
            LEFT JOIN
                dbc_spellvisualkit svk10 ON svk10.id = sv.instantAreaKitId
            LEFT JOIN
                dbc_spellvisualkit svk11 ON svk11.id = sv.impactAreaKitId
            LEFT JOIN
                dbc_spellvisualkit svk12 ON svk12.id = sv.persistentAreaKitId
        ');


        /***************/
        /* Zone Sounds */
        /***************/

        CLI::write(' - linking to zones');

        // omiting data from WMOAreaTable, as its at the moment impossible to link to actual zones

        DB::Aowow()->query('TRUNCATE ?_zones_sounds');
        DB::Aowow()->query('
            INSERT INTO
                ?_zones_sounds (id, ambienceDay, ambienceNight, musicDay, musicNight, intro, worldStateId, worldStateValue)
            SELECT
                a.id,
                IFNULL(sa1.soundIdDay, 0),
                IFNULL(sa1.soundIdNight, 0),
                IFNULL(zm1.soundIdDay, 0),
                IFNULL(zm1.soundIdNight, 0),
                IFNULL(zimt1.soundId, 0),
                0,
                0
            FROM
                dbc_areatable a
            LEFT JOIN
                dbc_soundambience sa1 ON sa1.id = a.soundAmbience
            LEFT JOIN
                dbc_zonemusic zm1 ON zm1.id = a.zoneMusic
            LEFT JOIN
                dbc_zoneintromusictable zimt1 ON zimt1.id = a.zoneIntroMusic
            WHERE
                a.soundAmbience > 0 OR a.zoneMusic > 0 OR a.zoneIntroMusic
            UNION
            SELECT
                IF(wszs.areaId, wszs.areaId, wszs.wmoAreaId),
                IFNULL(sa2.soundIdDay, 0),
                IFNULL(sa2.soundIdNight, 0),
                IFNULL(zm2.soundIdDay, 0),
                IFNULL(zm2.soundIdNight, 0),
                IFNULL(zimt2.soundId, 0),
                wszs.stateId,
                wszs.value
            FROM
                dbc_worldstatezonesounds wszs
            LEFT JOIN
                dbc_soundambience sa2 ON sa2.id = wszs.soundAmbienceId
            LEFT JOIN
                dbc_zonemusic zm2 ON zm2.id = wszs.zoneMusicId
            LEFT JOIN
                dbc_zoneintromusictable zimt2 ON zimt2.id = wszs.zoneIntroMusicId
            WHERE
                wszs.zoneMusicId > 0 AND (wszs.areaId OR wszs.wmoAreaId IN (?a))
        ', array_keys($worldStateZoneSoundFix));

        // apply post-fix
        foreach ($worldStateZoneSoundFix as $old => $new)
            DB::Aowow()->query('UPDATE ?_zones_sounds SET id = ?d WHERE id = ?d', $new, $old);


        /***************/
        /* Item Sounds */
        /***************/

        CLI::write(' - linking to items');

        DB::Aowow()->query('
            UPDATE
                ?_items i
            LEFT JOIN
                dbc_itemdisplayinfo idi ON
                    idi.id = i.displayId
            LEFT JOIN
                dbc_itemgroupsounds igs ON
                    igs.id = idi.groupSoundId
            LEFT JOIN
                dbc_material m ON
                    m.id = i.material
            SET
                i.spellVisualId    = IFNULL(idi.spellVisualId,   0),
                i.pickUpSoundId    = IFNULL(igs.pickUpSoundId,   0),
                i.dropDownSoundId  = IFNULL(igs.dropDownSoundId, 0),
                i.sheatheSoundId   = IFNULL(m.sheatheSoundId,    0),
                i.unsheatheSoundId = IFNULL(m.unsheatheSoundId,  0)
        ');

        DB::Aowow()->query('TRUNCATE ?_items_sounds');

        $fields = ['hit', 'crit'];
        foreach ($fields as $f)
        {
            for ($i = 1; $i <= 10; $i++)
            {
                DB::Aowow()->query('
                    INSERT INTO
                        ?_items_sounds
                    SELECT
                        ?#,
                        (1 << wis.subClass)
                    FROM
                        dbc_weaponimpactsounds wis
                    WHERE
                        ?# > 0
                    ON DUPLICATE KEY UPDATE
                        subClassMask = subClassMask | (1 << wis.subClass)
                ', $f.$i, $f.$i);
            }
        }

        DB::Aowow()->query('
            INSERT INTO
                ?_items_sounds
            SELECT
                wss.soundId,
                (1 << isc.subClass)
            FROM
                dbc_itemsubclass isc
            JOIN
                dbc_weaponswingsounds2 wss ON
                    wss.weaponSize = isc.weaponSize
            WHERE
                isc.class = 2
            ON DUPLICATE KEY UPDATE
                subClassMask = subClassMask | (1 << isc.subClass)
        ');


        /************************/
        /* Screen Effect Sounds */
        /************************/

        CLI::write(' - linking to screen effects');

        DB::Aowow()->query('TRUNCATE ?_screeneffect_sounds');
        DB::Aowow()->query('
            INSERT INTO
                ?_screeneffect_sounds
            SELECT
                se.id, se.name, IFNULL(sa.soundIdDay, 0), IFNULL(sa.soundIdNight, 0), IFNULL(zm.soundIdDay, 0), IFNULL(zm.soundIdNight, 0)
            FROM
                dbc_screeneffect se
            LEFT JOIN
                dbc_soundambience sa ON se.soundAmbienceId = sa.id
            LEFT JOIN
                dbc_zonemusic zm ON se.zoneMusicId = zm.id
        ');

        $this->reapplyCCFlags('sounds', Type::SOUND);

        return true;
    }
});

?>
