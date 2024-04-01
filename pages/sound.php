<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 19: Sound    g_initPath()
//  tabId  0: Database g_initHeader()
class SoundPage extends GenericPage
{
    use TrDetailPage;

    protected $articleUrl    = '';

    protected $type          = Type::SOUND;
    protected $typeId        = 0;
    protected $tpl           = 'sound';
    protected $path          = [0, 19];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    protected $special       = false;
    protected $_get          = ['playlist' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkEmptySet']];

    private   $cat           = 0;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // special case
        if (!$id && $this->_get['playlist'])
        {
            $this->special    = true;
            $this->name       = Lang::sound('cat', 1000);
            $this->cat        = 1000;
            $this->articleUrl = 'sound&playlist';
            $this->contribute = CONTRIBUTE_NONE;
            $this->mode       = CACHE_TYPE_NONE;
        }
        // regular case
        else
        {
            $this->typeId = intVal($id);

            $this->subject = new SoundList(array(['id', $this->typeId]));
            if ($this->subject->error)
                $this->notFound(Lang::game('sound'), Lang::sound('notFound'));

            $this->name = $this->subject->getField('name');
            $this->cat  = $this->subject->getField('cat');
        }
    }

    protected function generatePath()
    {
        $this->path[] = $this->cat;
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('sound')));
    }

    protected function generateContent()
    {
        if ($this->special)
            $this->generatePlaylistContent();
        else
            $this->generateDefaultContent();
    }

    private function generatePlaylistContent()
    {

    }

    private function generateDefaultContent()
    {
        /****************/
        /* Main Content */
        /****************/

        $this->addScript([SC_JS_FILE, '?data=zones']);

        // get spawns
        $map = null;
        if ($spawns = $this->subject->getSpawns(SPAWNINFO_FULL))
        {
            $map = ['data' => ['parent' => 'mapper-generic'], 'mapperData' => &$spawns];
            foreach ($spawns as $areaId => &$areaData)
                $map['extra'][$areaId] = ZoneList::getName($areaId);
        }

        // get full path ingame for sound (workaround for missing PlaySoundKit())
        $fullpath = DB::Aowow()->selectCell('SELECT IF(sf.`path` <> "", CONCAT(sf.`path`, "\\\\", sf.`file`), sf.`file`) FROM ?_sounds_files sf JOIN ?_sounds s ON s.soundFile1 = sf.id WHERE s.id = ?d', $this->typeId);

        $this->map          = $map;
        $this->headIcons  = [$this->subject->getField('iconString')];
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


        /**************/
        /* Extra Tabs */
        /**************/

        // tab: Spells
        // skipping (always empty): ready, castertargeting, casterstate, targetstate
        $displayIds = DB::Aowow()->selectCol('
            SELECT id FROM ?_spell_sounds WHERE
                animation = ?d OR
                precast = ?d OR
                cast = ?d OR
                impact = ?d OR
                state = ?d OR
                statedone = ?d OR
                channel = ?d OR
                casterimpact = ?d OR
                targetimpact = ?d OR
                missiletargeting = ?d OR
                instantarea = ?d OR
                persistentarea = ?d OR
                missile = ?d OR
                impactarea = ?d
        ', $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId);

        $seMiscValues = DB::Aowow()->selectCol('
            SELECT `id` FROM ?_screeneffect_sounds WHERE
                `ambienceDay` = ?d OR
                `ambienceNight` = ?d OR
                `musicDay` = ?d OR
                `musicNight` = ?d
        ', $this->typeId, $this->typeId, $this->typeId, $this->typeId);

        $cnd = array(
            'OR',
            ['AND', ['effect1Id', 132], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', 132], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', 132], ['effect3MiscValue', $this->typeId]]
        );

        if ($displayIds)
            $cnd[] = ['spellVisualId', $displayIds];

        if ($seMiscValues)
            $cnd[] = array(
                'OR',
                ['AND', ['effect1AuraId', 260], ['effect1MiscValue', $seMiscValues]],
                ['AND', ['effect2AuraId', 260], ['effect2MiscValue', $seMiscValues]],
                ['AND', ['effect3AuraId', 260], ['effect3MiscValue', $seMiscValues]]
            );

        $spells = new SpellList($cnd);
        if (!$spells->error)
        {
            $this->extendGlobalData($spells->getJSGlobals(GLOBALINFO_SELF));
            $this->lvTabs[] = [SpellList::$brickFile, ['data' => array_values($spells->getListviewData())]];
        }


        // tab: Items
        $subClasses = [];
        if ($subClassMask = DB::Aowow()->selectCell('SELECT subClassMask FROM ?_items_sounds WHERE soundId = ?d', $this->typeId))
            for ($i = 0; $i <= 20; $i++)
                if ($subClassMask & (1 << $i))
                    $subClasses[] = $i;

        $itemIds = DB::Aowow()->selectCol('
            SELECT
                id
            FROM
                ?_items
            WHERE
               {spellVisualId    IN (?a) OR }
                pickUpSoundId    =   ?d  OR
                dropDownSoundId  =   ?d  OR
                sheatheSoundId   =   ?d  OR
                unsheatheSoundId =   ?d {OR
                (
                    IF (soundOverrideSubclass > 0, soundOverrideSubclass, subclass) IN (?a) AND
                    class = ?d
                )}
        ', $displayIds ?: DBSIMPLE_SKIP, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $subClasses ?: DBSIMPLE_SKIP, ITEM_CLASS_WEAPON);
        if ($itemIds)
        {
            $items = new ItemList(array(['id', $itemIds]));
            if (!$items->error)
            {
                $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = [ItemList::$brickFile, ['data' => array_values($items->getListviewData())]];
            }
        }


        // tab: Zones
        if ($zoneIds = DB::Aowow()->select('SELECT id, worldStateId, worldStateValue FROM ?_zones_sounds WHERE ambienceDay = ?d OR ambienceNight = ?d OR musicDay = ?d OR musicNight = ?d OR intro = ?d', $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId))
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

                if (array_filter(array_column($zoneIds, 'worldStateId')))
                {
                    $tabData['extraCols']  = ['$Listview.extraCols.condition'];

                    foreach ($zoneIds as $zData)
                        if ($zData['worldStateId'])
                            Conditions::extendListviewRow($zoneData[$zData['id']], Conditions::SRC_NONE, $this->typeId, [Conditions::WORLD_STATE, $zData['worldStateId'], $zData['worldStateValue']]);
                }

                $tabData['data'] = array_values($zoneData);
                $tabData['hiddenCols'] = ['territory'];

                $this->lvTabs[] = [ZoneList::$brickFile, $tabData];
            }
        }


        // tab: Races (VocalUISounds (containing error voice overs))
        if ($vo = DB::Aowow()->selectCol('SELECT raceId FROM ?_races_sounds WHERE soundId = ?d GROUP BY raceId', $this->typeId))
        {
            $races = new CharRaceList(array(['id', $vo]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = [CharRaceList::$brickFile, ['data' => array_values($races->getListviewData())]];
            }
        }


        // tab: Emotes (EmotesTextSound (containing emote audio))
        if ($em = DB::Aowow()->selectCol('SELECT emoteId FROM ?_emotes_sounds WHERE soundId = ?d GROUP BY emoteId UNION SELECT id FROM ?_emotes WHERE soundId = ?d', $this->typeId, $this->typeId))
        {
            $races = new EmoteList(array(['id', $em]));
            if (!$races->error)
            {
                $this->extendGlobalData($races->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = [EmoteList::$brickFile, array(
                    'data' => array_values($races->getListviewData()),
                    'name' => Util::ucFirst(Lang::game('emotes'))
                ), 'emote'];
            }
        }

        $creatureIds = DB::World()->selectCol('SELECT ct.CreatureID FROM creature_text ct LEFT JOIN broadcast_text bct ON bct.ID = ct.BroadCastTextId WHERE bct.SoundEntriesID = ?d OR ct.Sound = ?d', $this->typeId, $this->typeId);

        // can objects or areatrigger play sound...?
        if ($goosp = SmartAI::getOwnerOfSoundPlayed($this->typeId, Type::NPC))
            $creatureIds = array_merge($creatureIds, $goosp[Type::NPC]);

        // tab: NPC (dialogues...?, generic creature sound)
        // skipping (always empty): transforms, footsteps
        $displayIds = DB::Aowow()->selectCol('
            SELECT id FROM ?_creature_sounds WHERE
                greeting = ?d OR
                farewell = ?d OR
                angry = ?d OR
                exertion = ?d OR
                exertioncritical = ?d OR
                injury = ?d OR
                injurycritical = ?d OR
                death = ?d OR
                stun = ?d OR
                stand = ?d OR
                aggro = ?d OR
                wingflap = ?d OR
                wingglide = ?d OR
                alert = ?d OR
                fidget = ?d OR
                customattack = ?d OR
                `loop` = ?d OR
                jumpstart = ?d OR
                jumpend = ?d OR
                petattack = ?d OR
                petorder = ?d OR
                petdismiss = ?d OR
                birth = ?d OR
                spellcast = ?d OR
                submerge = ?d OR
                submerged = ?d
        ', $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId);

        // broadcast_text <-> creature_text
        if ($creatureIds || $displayIds)
        {
            $extra = [];
            $cnds = [Cfg::get('SQL_LIMIT_NONE'), &$extra];
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
                $this->addScript([SC_JS_FILE, '?data=zones']);

                $this->extendGlobalData($npcs->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs[] = [CreatureList::$brickFile, ['data' => array_values($npcs->getListviewData())]];
            }
        }
    }
}


?>
