<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SoundBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'sound';
    protected  string $pageName   = 'sound';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 19];

    public int $type   = Type::SOUND;
    public int $typeId = 0;

    private SoundList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new SoundList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('sound'), Lang::sound('notFound'));

        $this->h1 = $this->subject->getField('name');

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_cat  = $this->subject->getField('cat');


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $_cat;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('sound')));


        /****************/
        /* Main Content */
        /****************/

        // get spawns
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $this->addDataLoader('zones');
            $this->map = array(
                ['parent' => 'mapper-generic'],             // Mapper
                $spawns,                                    // mapperData
                null,                                       // ShowOnMap
                [Lang::sound('foundIn')]                    // foundIn
            );
            foreach ($spawns as $areaId => $__)
                $this->map[3][$areaId] = ZoneList::getName($areaId);
        }

        // get full path in-game for sound (workaround for missing PlaySoundKit())
        $fullpath = DB::Aowow()->selectCell('SELECT IF(sf.`path` <> "", CONCAT(sf.`path`, "\\\\", sf.`file`), sf.`file`) FROM ?_sounds_files sf JOIN ?_sounds s ON s.`soundFile1` = sf.`id` WHERE s.`id` = ?d', $this->typeId);

        $this->redButtons = array(
            BUTTON_WOWHEAD  => true,
            BUTTON_PLAYLIST => true,
            BUTTON_LINKS    => array(
                'type'   => Type::SOUND,
                'typeId' => $this->typeId,
                'sound'  => str_replace('\\', '\\\\', $fullpath) // escape for wow client
            )
        );

        $this->extendGlobalData($this->subject->getJSGlobals());

        parent::generate();


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: Spells
        // skipping (always empty): ready, castertargeting, casterstate, targetstate
        $displayIds = DB::Aowow()->selectCol(
           'SELECT `id`
            FROM   ?_spell_sounds
            WHERE  `animation`   = ?d OR `precast`        = ?d OR `cast`         = ?d OR `impact`       = ?d OR `state` = ?d OR
                   `statedone`   = ?d OR `channel`        = ?d OR `casterimpact` = ?d OR `targetimpact` = ?d OR `missiletargeting` = ?d OR
                   `instantarea` = ?d OR `persistentarea` = ?d OR `missile`      = ?d OR `impactarea`   = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );

        $seMiscValues = DB::Aowow()->selectCol(
           'SELECT `id`
            FROM   ?_screeneffect_sounds
            WHERE  `ambienceDay` = ?d OR `ambienceNight` = ?d OR `musicDay` = ?d OR `musicNight` = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );

        $cnd = array(
            'OR',
            ['AND', ['effect1Id', [SPELL_EFFECT_PLAY_MUSIC, SPELL_EFFECT_PLAY_SOUND]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', [SPELL_EFFECT_PLAY_MUSIC, SPELL_EFFECT_PLAY_SOUND]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', [SPELL_EFFECT_PLAY_MUSIC, SPELL_EFFECT_PLAY_SOUND]], ['effect3MiscValue', $this->typeId]]
        );

        if ($displayIds)
            $cnd[] = ['spellVisualId', $displayIds];

        if ($seMiscValues)
            $cnd[] = array(
                'OR',
                ['AND', ['effect1AuraId', SPELL_AURA_SCREEN_EFFECT], ['effect1MiscValue', $seMiscValues]],
                ['AND', ['effect2AuraId', SPELL_AURA_SCREEN_EFFECT], ['effect2MiscValue', $seMiscValues]],
                ['AND', ['effect3AuraId', SPELL_AURA_SCREEN_EFFECT], ['effect3MiscValue', $seMiscValues]]
            );

        $spells = new SpellList($cnd);
        if (!$spells->error)
        {
            $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));
            $this->lvTabs->addListviewTab(new Listview(['data' => $spells->getListviewData()], SpellList::$brickFile));
        }

        // tab: Items
        $subClasses = [];
        if ($subClassMask = DB::Aowow()->selectCell('SELECT `subClassMask` FROM ?_items_sounds WHERE `soundId` = ?d', $this->typeId))
            for ($i = 0; $i <= 20; $i++)
                if ($subClassMask & (1 << $i))
                    $subClasses[] = $i;

        $itemIds = DB::Aowow()->selectCol(
           'SELECT id
            FROM   ?_items
            WHERE  `pickUpSoundId` = ?d OR `dropDownSoundId` = ?d OR `sheatheSoundId` = ?d OR `unsheatheSoundId` = ?d
                   { OR `spellVisualId` IN (?a) }
                   { OR (IF (`soundOverrideSubclass` > 0, `soundOverrideSubclass`, `subclass`) IN (?a) AND `class` = ?d) }',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $displayIds ?: DBSIMPLE_SKIP, $subClasses ?: DBSIMPLE_SKIP, ITEM_CLASS_WEAPON
        );
        if ($itemIds)
        {
            $items = new ItemList(array(['id', $itemIds]));
            if (!$items->error)
            {
                $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(['data' => $items->getListviewData()], ItemList::$brickFile));
            }
        }

        // tab: Zones
        if ($zoneIds = DB::Aowow()->select('SELECT `id`, `worldStateId`, `worldStateValue` FROM ?_zones_sounds WHERE `ambienceDay` = ?d OR `ambienceNight` = ?d OR `musicDay` = ?d OR `musicNight` = ?d OR `intro` = ?d', $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId))
        {
            $zones = new ZoneList(array(['id', array_column($zoneIds, 'id')]));
            if (!$zones->error)
            {
                $this->extendGlobalData($zones->getJSGlobals(GLOBALINFO_SELF));

                $zoneData = $zones->getListviewData();
                $parents  = $zones->getAllFields('parentArea');
                $tabData  = [];
                $pIds     = array_filter(array_unique(array_values($parents)));
                if ($pIds)
                {
                    $pZones = new ZoneList(array(['id', $pIds]));
                    if (!$pZones->error)
                    {
                        $this->extendGlobalData($pZones->getJSGlobals(GLOBALINFO_SELF));

                        $pData = $pZones->getListviewData();
                        foreach ($parents as $child => $parent)
                        {
                            if (!$parent || empty($pData[$parent]))
                                continue;

                            if (!isset($pData[$parent]['subzones']))
                                $pData[$parent]['subzones'] = [];

                            $pData[$parent]['subzones'][] = $child;
                            unset($parents[$child]);
                        }

                        // these are original parents
                        foreach ($parents as $parent => $__)
                            if (empty($pData[$parent]))
                                $pData[$parent] = $zoneData[$parent];

                        $zoneData = $pData;
                    }
                }

                if ($worldStates = array_filter($zoneIds, fn($x) => $x['worldStateId'] > 0))
                {
                    $tabData['extraCols']  = ['$Listview.extraCols.condition'];

                    foreach ($worldStates as $state)
                    {
                        if (isset($zoneData[$state['id']]))
                            Conditions::extendListviewRow($zoneData[$state['id']], Conditions::SRC_NONE, $this->typeId, [Conditions::WORLD_STATE, $state['worldStateId'], $state['worldStateValue']]);
                        else
                            foreach ($zoneData as &$d)
                                if (in_array($state['id'], $d['subzones'] ?? []))
                                    Conditions::extendListviewRow($d, Conditions::SRC_NONE, $this->typeId, [Conditions::WORLD_STATE, $state['worldStateId'], $state['worldStateValue']]);
                    }
                }

                $tabData['data'] = $zoneData;
                $tabData['hiddenCols'] = ['territory'];

                $this->lvTabs->addListviewTab(new Listview($tabData, ZoneList::$brickFile));
            }
        }

        // tab: Races (VocalUISounds (containing error voice overs))
        if ($vo = DB::Aowow()->selectCol('SELECT `raceId` FROM ?_races_sounds WHERE `soundId` = ?d GROUP BY `raceId`', $this->typeId))
        {
            $races = new CharRaceList(array(['id', $vo]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(['data' => $races->getListviewData()], CharRaceList::$brickFile));
            }
        }

        // tab: Emotes (EmotesTextSound (containing emote audio))
        if ($em = DB::Aowow()->selectCol('SELECT `emoteId` FROM ?_emotes_sounds WHERE `soundId` = ?d GROUP BY `emoteId` UNION SELECT `id` FROM ?_emotes WHERE `soundId` = ?d', $this->typeId, $this->typeId))
        {
            $races = new EmoteList(array(['id', $em]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $races->getListviewData(),
                    'name' => Util::ucFirst(Lang::game('emotes'))
                ), EmoteList::$brickFile, 'emote'));
            }
        }

        $creatureIds = DB::World()->selectCol('SELECT ct.`CreatureID` FROM creature_text ct LEFT JOIN broadcast_text bct ON bct.`ID` = ct.`BroadCastTextId` WHERE bct.`SoundEntriesID` = ?d OR ct.`Sound` = ?d', $this->typeId, $this->typeId);

        // can objects or areatrigger play sound...?
        if ($goosp = SmartAI::getOwnerOfSoundPlayed($this->typeId, Type::NPC))
            $creatureIds = array_merge($creatureIds, $goosp[Type::NPC]);

        // tab: NPC (dialogues...?, generic creature sound)
        // skipping (always empty): transforms, footsteps
        $displayIds = DB::Aowow()->selectCol(
           'SELECT `id`
            FROM   ?_creature_sounds
            WHERE  `greeting`     = ?d OR `farewell`       = ?d OR `angry`     = ?d OR `exertion`  = ?d OR `exertioncritical` = ?d OR
                   `injury`       = ?d OR `injurycritical` = ?d OR `death`     = ?d OR `stun`      = ?d OR `stand`            = ?d OR
                   `aggro`        = ?d OR `wingflap`       = ?d OR `wingglide` = ?d OR `alert`     = ?d OR `fidget`           = ?d OR
                   `customattack` = ?d OR `loop`           = ?d OR `jumpstart` = ?d OR `jumpend`   = ?d OR `petattack`        = ?d OR
                   `petorder`     = ?d OR `petdismiss`     = ?d OR `birth`     = ?d OR `spellcast` = ?d OR `submerge`         = ?d OR `submerged` = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId,
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId,
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId,
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId,
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );

        // broadcast_text <-> creature_text
        if ($creatureIds || $displayIds)
        {
            $extra = [];
            $cnds  = [&$extra];
            if (!User::isInGroup(U_GROUP_STAFF))
                $cnds[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

            if ($creatureIds)
                $extra[] = ['id', $creatureIds];

            if ($displayIds)
                $extra[] = ['displayId1', $displayIds];

            if (count($extra) > 1)
                array_unshift($extra, 'OR');
            else
                $extra = array_pop($extra);

            $npcs = new CreatureList($cnds);
            if (!$npcs->error)
            {
                $this->extendGlobalData($npcs->getJSGlobals(GLOBALINFO_SELF));

                $this->addDataLoader('zones');
                $this->lvTabs->addListviewTab(new Listview(['data' => $npcs->getListviewData()], CreatureList::$brickFile));
            }
        }
    }
}


?>
