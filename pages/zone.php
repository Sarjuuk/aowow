<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 6: Zone     g_initPath()
//  tabId 0: Database g_initHeader()
class ZonePage extends GenericPage
{
    use TrDetailPage;

    protected $path      = [0, 6];
    protected $tabId     = 0;
    protected $type      = Type::ZONE;
    protected $typeId    = 0;
    protected $tpl       = 'detail-page-generic';
    protected $scripts   = [[SC_JS_FILE, 'js/ShowOnMap.js']];

    protected $zoneMusic = [];

    public function __construct($pageCall, $id)
    {
        $this->typeId = intVal($id);

        parent::__construct($pageCall, $id);

        $this->subject = new ZoneList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('zone'), Lang::zone('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generateContent()
    {
        $this->addScript([SC_JS_FILE, '?data=zones']);

        $parentArea = $this->subject->getField('parentArea');


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // City
        if ($this->subject->getField('flags') & 0x8 && !$parentArea)
            $infobox[] = Lang::zone('city');

        // Auto repop
        if ($this->subject->getField('flags') & 0x1000 && !$parentArea)
            $infobox[] = Lang::zone('autoRez');

        // Level
        if ($_ = $this->subject->getField('levelMin'))
        {
            if ($_ < $this->subject->getField('levelMax'))
                $_ .= ' - '.$this->subject->getField('levelMax');

            $infobox[] = Lang::game('level').Lang::main('colon').$_;
        }

        // required Level
        if ($_ = $this->subject->getField('levelReq'))
        {
            if ($__ = $this->subject->getField('levelReqLFG'))
                $buff = sprintf(Lang::zone('reqLevels'), $_, $__);
            else
                $buff = Lang::main('_reqLevel').Lang::main('colon').$_;

            $infobox[] = $buff;
        }

        // Territory
        $_  = $this->subject->getField('faction');
        $__ = '%s';
        if ($_ == 0)
            $__ = '[span class=icon-alliance]%s[/span]';
        else if ($_ == 1)
            $__ = '[span class=icon-horde]%s[/span]';
        else if ($_ == 4)
            $__ = '[span class=icon-ffa]%s[/span]';

        $infobox[] = Lang::zone('territory').Lang::main('colon').sprintf($__, Lang::zone('territories', $_));

        // Instance Type
        $infobox[] = Lang::zone('instanceType').Lang::main('colon').'[span class=icon-instance'.$this->subject->getField('type').']'.Lang::zone('instanceTypes', $this->subject->getField('type')).'[/span]';

        // Heroic mode
        if ($_ = $this->subject->getField('levelHeroic'))
            $infobox[] = '[icon preset=heroic]'.sprintf(Lang::zone('hcAvailable'), $_).'[/icon]';

        // number of players
        if ($_ = $this->subject->getField('maxPlayer'))
            $infobox[] = Lang::zone('numPlayers').Lang::main('colon').($_ == -2 ? '10/25' : $_);

        // Attunement Quest/Achievements & Keys
        if ($attmnt = $this->subject->getField('attunes'))
        {
            foreach ($attmnt as $type => $ids)
            {
                $this->extendGlobalIds($type, ...array_map('abs', $ids));
                foreach ($ids as $id)
                {
                    if ($type == Type::ITEM)
                        $infobox[] = Lang::zone('key', (int)($id < 0)).Lang::main('colon').'[item='.abs($id).']';
                    else
                        $infobox[] = Lang::zone('attunement', (int)($id < 0)).Lang::main('colon').'['.Type::getFileString($type).'='.abs($id).']';
                }
            }
        }

        // Instances
        if ($_ = DB::Aowow()->selectCol('SELECT `id` FROM ?_zones WHERE `parentAreaId` = ?d AND (`cuFlags` & ?d) = 0', $this->typeId, CUSTOM_EXCLUDE_FOR_LISTVIEW))
        {
            $this->extendGlobalIds(Type::ZONE, ...$_);
            $infobox[] = Lang::maps('Instances').Lang::main('colon')."\n[zone=".implode("], \n[zone=", $_).']';
        }

        // location (if instance)
        if ($pa = $this->subject->getField('parentAreaId'))
        {
            $paO = new ZoneList(array(['id', $pa]));
            if (!$paO->error)
            {
                $pins = str_pad($this->subject->getField('parentX') * 10, 3, '0', STR_PAD_LEFT) . str_pad($this->subject->getField('parentY') * 10, 3, '0', STR_PAD_LEFT);
                $infobox[] = Lang::zone('location').Lang::main('colon').'[lightbox=map zone='.$pa.' pins='.$pins.']'.$paO->getField('name', true).'[/lightbox]';
            }
        }

/*  has to be defined in an article, i think

    // faction(s) / Reputation Hub / Raid Faction
    // [li]Raid faction: [faction=1156][/li] || [li]Factions: [faction=1156]/[faction=1156][/li]

    // final boss
    // [li]Final boss: [icon preset=boss][npc=37226][/icon][/li]
*/

        /****************/
        /* Main Content */
        /****************/

        $addToSOM = function ($what, $entry) use (&$som)
        {
            // entry always contains: type, id, name, level, coords[]
            if (!isset($som[$what][$entry['name']]))        // not found yet
                $som[$what][$entry['id']][] = $entry;
            else                                            // found .. something..
            {
                // check for identical floors
                foreach ($som[$what][$entry['id']] as &$byFloor)
                {
                    if ($byFloor['level'] != $entry['level'])
                        continue;

                    // found existing floor, ammending coords
                    $byFloor['coords'][] = $entry['coords'][0];
                    return;
                }

                // floor not used yet, create it
                $som[$what][$entry['id']][] = $entry;
            }
        };

        if ($parentArea)
        {
            $this->extraText = sprintf(Lang::zone('zonePartOf'), $parentArea);
            $this->extendGlobalIds(Type::ZONE, $parentArea);
        }

        // we cannot fetch spawns via lists. lists are grouped by entry
        $oSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, Type::OBJECT);
        $cSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, Type::NPC);
        $aSpawns = User::isInGroup(U_GROUP_STAFF) ? DB::Aowow()->select('SELECT * FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, Type::AREATRIGGER) : [];

        $conditions = [CFG_SQL_LIMIT_NONE, ['s.areaId', $this->typeId]];
        if (!User::isInGroup(U_GROUP_STAFF))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $objectSpawns   = new GameObjectList($conditions);
        $creatureSpawns = new CreatureList($conditions);
        $atSpawns       = new AreaTriggerList($conditions);

        $questsLV = $rewardsLV = [];

        $relQuestZOS = [$this->typeId];
        foreach (Game::$questSubCats as $parent => $children)
        {
            if (in_array($this->typeId, $children))
                $relQuestZOS[] = $parent;
            else if ($this->typeId == $parent)
                $relQuestZOS = array_merge($relQuestZOS, $children);
        }

        // see if we can actually display a map
        $hasMap = file_exists('static/images/wow/maps/'.Util::$localeStrings[User::$localeId].'/normal/'.$this->typeId.'.jpg');
        if (!$hasMap)                                       // try multilayered
            $hasMap = file_exists('static/images/wow/maps/'.Util::$localeStrings[User::$localeId].'/normal/'.$this->typeId.'-1.jpg');
        if (!$hasMap)                                       // try english fallback
            $hasMap = file_exists('static/images/wow/maps/enus/normal/'.$this->typeId.'.jpg');
        if (!$hasMap)                                       // try english fallback, multilayered
            $hasMap = file_exists('static/images/wow/maps/enus/normal/'.$this->typeId.'-1.jpg');

        if ($hasMap)
        {
            $som = [];
            foreach ($oSpawns as $spawn)
            {
                $tpl = $objectSpawns->getEntry($spawn['typeId']);
                if (!$tpl)
                    continue;

                $n = Util::localizedString($tpl, 'name');

                $what = '';
                switch ($tpl['typeCat'])
                {
                    case -3:
                        $what = 'herb';
                        break;
                    case -4:
                        $what = 'vein';
                        break;
                    case  9:
                        $what = 'book';
                        break;
                    case 25:
                        $what = 'pool';
                        break;
                    case 0:
                        if ($tpl['type'] == 19)
                            $what = 'mail';
                        break;
                    case -6:
                        if ($tpl['spellFocusId'] == 1)
                            $what = 'anvil';
                        else if ($tpl['spellFocusId'] == 3)
                            $what = 'forge';

                        break;
                }

                if ($what)
                {
                    $blob = array(
                        'coords' => [[$spawn['posX'], $spawn['posY']]],
                        'level'  => $spawn['floor'],
                        'name'   => $n,
                        'type'   => Type::OBJECT,
                        'id'     => $tpl['id']
                    );

                    if ($what == 'mail')
                       $blob['side'] = (($tpl['A'] < 0 ? 0 : 0x1) | ($tpl['H'] < 0 ? 0 : 0x2));

                    $addToSOM($what, $blob);
                }

                if ($tpl['startsQuests'])
                {
                        $started = new QuestList(array(['qse.method', 1, '&'], ['qse.type', Type::OBJECT], ['qse.typeId', $tpl['id']]));
                        if ($started->error)
                            continue;

                        // store data for misc tabs
                        foreach ($started->getListviewData() as $id => $data)
                        {
                            if ($started->getField('zoneOrSort') > 0 && !in_array($started->getField('zoneOrSort'), $relQuestZOS))
                                continue;

                            if (!empty($started->rewards[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->rewards[$id][Type::ITEM]));

                            if (!empty($started->choices[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->choices[$id][Type::ITEM]));

                            $questsLV[$id] = $data;
                        }

                        $this->extendGlobalData($started->getJSGlobals());

                        if (($tpl['A'] != -1) && ($_ = $started->getSOMData(SIDE_ALLIANCE)))
                            $addToSOM('alliancequests', array(
                                'coords' => [[$spawn['posX'], $spawn['posY']]],
                                'level'  => $spawn['floor'],
                                'name'   => $n,
                                'type'   => Type::OBJECT,
                                'id'     => $tpl['id'],
                                'side'   => (($tpl['A'] < 0 ? 0 : 0x1) | ($tpl['H'] < 0 ? 0 : 0x2)),
                                'quests' => array_values($_)
                            ));

                        if (($tpl['H'] != -1) && ($_ = $started->getSOMData(SIDE_HORDE)))
                            $addToSOM('hordequests', array(
                                'coords' => [[$spawn['posX'], $spawn['posY']]],
                                'level'  => $spawn['floor'],
                                'name'   => $n,
                                'type'   => Type::OBJECT,
                                'id'     => $tpl['id'],
                                'side'   => (($tpl['A'] < 0 ? 0 : 0x1) | ($tpl['H'] < 0 ? 0 : 0x2)),
                                'quests' => array_values($_)
                            ));
                }
            }

            $flightNodes = [];
            foreach ($cSpawns as $spawn)
            {
                $tpl = $creatureSpawns->getEntry($spawn['typeId']);
                if (!$tpl)
                    continue;

                $n  = Util::localizedString($tpl, 'name');
                $sn = Util::localizedString($tpl, 'subname');

                $what = '';
                if ($tpl['npcflag'] & NPC_FLAG_REPAIRER)
                    $what = 'repair';
                else if ($tpl['npcflag'] & NPC_FLAG_AUCTIONEER)
                    $what = 'auctioneer';
                else if ($tpl['npcflag'] & NPC_FLAG_BANKER)
                    $what = 'banker';
                else if ($tpl['npcflag'] & NPC_FLAG_BATTLEMASTER)
                    $what = 'battlemaster';
                else if ($tpl['npcflag'] & NPC_FLAG_INNKEEPER)
                    $what = 'innkeeper';
                else if ($tpl['npcflag'] & NPC_FLAG_TRAINER)
                    $what = 'trainer';
                else if ($tpl['npcflag'] & NPC_FLAG_VENDOR)
                    $what = 'vendor';
                else if ($tpl['npcflag'] & NPC_FLAG_FLIGHT_MASTER)
                {
                    $flightNodes[$tpl['id']] = [$spawn['posX'], $spawn['posY']];
                    $what = 'flightmaster';
                }
                else if ($tpl['npcflag'] & NPC_FLAG_STABLE_MASTER)
                    $what = 'stablemaster';
                else if ($tpl['npcflag'] & NPC_FLAG_GUILD_MASTER)
                    $what = 'guildmaster';
                else if ($tpl['npcflag'] & (NPC_FLAG_SPIRIT_HEALER | NPC_FLAG_SPIRIT_GUIDE))
                    $what = 'spirithealer';
                else if ($creatureSpawns->isBoss())
                    $what = 'boss';
                else if ($tpl['rank'] == 2 || $tpl['rank'] == 4)
                    $what = 'rare';

                if ($what)
                    $addToSOM($what, array(
                        'coords'        => [[$spawn['posX'], $spawn['posY']]],
                        'level'         => $spawn['floor'],
                        'name'          => $n,
                        'type'          => Type::NPC,
                        'id'            => $tpl['id'],
                        'reacthorde'    => $tpl['H'] ?: 1,      // no neutral (0) setting
                        'reactalliance' => $tpl['A'] ?: 1,
                        'description'   => $sn
                    ));

                if ($tpl['startsQuests'])
                {
                        $started = new QuestList(array(['qse.method', 1, '&'], ['qse.type', Type::NPC], ['qse.typeId', $tpl['id']]));
                        if ($started->error)
                            continue;

                        // store data for misc tabs
                        foreach ($started->getListviewData() as $id => $data)
                        {
                            if ($started->getField('zoneOrSort') > 0 && !in_array($started->getField('zoneOrSort'), $relQuestZOS))
                                continue;

                            if (!empty($started->rewards[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->rewards[$id][Type::ITEM]));

                            if (!empty($started->choices[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->choices[$id][Type::ITEM]));

                            $questsLV[$id] = $data;
                        }

                        $this->extendGlobalData($started->getJSGlobals());

                        if (($tpl['A'] != -1) && ($_ = $started->getSOMData(SIDE_ALLIANCE)))
                            $addToSOM('alliancequests', array(
                                'coords'        => [[$spawn['posX'], $spawn['posY']]],
                                'level'         => $spawn['floor'],
                                'name'          => $n,
                                'type'          => Type::NPC,
                                'id'            => $tpl['id'],
                                'reacthorde'    => $tpl['H'],
                                'reactalliance' => $tpl['A'],
                                'side'          => (($tpl['A'] < 0 ? 0 : SIDE_ALLIANCE) | ($tpl['H'] < 0 ? 0 : SIDE_HORDE)),
                                'quests'        => array_values($_)
                            ));

                        if (($tpl['H'] != -1) && ($_ = $started->getSOMData(SIDE_HORDE)))
                            $addToSOM('hordequests', array(
                                'coords'        => [[$spawn['posX'], $spawn['posY']]],
                                'level'         => $spawn['floor'],
                                'name'          => $n,
                                'type'          => Type::NPC,
                                'id'            => $tpl['id'],
                                'reacthorde'    => $tpl['H'],
                                'reactalliance' => $tpl['A'],
                                'side'          => (($tpl['A'] < 0 ? 0 : SIDE_ALLIANCE) | ($tpl['H'] < 0 ? 0 : SIDE_HORDE)),
                                'quests'        => array_values($_)
                            ));
                }
            }

            foreach ($aSpawns as $spawn)
            {
                $tpl = $atSpawns->getEntry($spawn['typeId']);
                if (!$tpl)
                    continue;

                $addToSOM('areatrigger', array(
                    'coords'        => [[$spawn['posX'], $spawn['posY']]],
                    'level'         => $spawn['floor'],
                    'name'          => Util::localizedString($tpl, 'name', true, true),
                    'type'          => Type::AREATRIGGER,
                    'id'            => $spawn['typeId'],
                    'description'   => 'Type: '.Lang::areatrigger('types', $tpl['type'])
                ));
            }

            // remove unwanted indizes
            foreach ($som as $what => &$dataz)
            {
                if (empty($som[$what]))
                    continue;

                foreach ($dataz as &$data)
                    $data = array_values($data);

                if (!in_array($what, ['vein', 'herb', 'rare', 'pool']))
                {
                    $foo = [];
                    foreach ($dataz as $d)
                        foreach ($d as $_)
                            $foo[] = $_;

                    $dataz = $foo;
                }
            }

            unset($data);

            // append paths between nodes
            if ($flightNodes)
            {
                // neutral nodes come last as the line is colored by the node it's attached to
                usort($som['flightmaster'], function($a, $b) {
                    $n1 = $a['reactalliance'] == $a['reacthorde'];
                    $n2 = $b['reactalliance'] == $b['reacthorde'];

                    if ($n1 && !$n2)
                        return 1;

                    if (!$n1 && $n2)
                        return -1;

                    return 0;
                });

                $paths = DB::Aowow()->select('SELECT n1.typeId AS "0", n2.typeId AS "1" FROM ?_taxipath p JOIN ?_taxinodes n1 ON n1.id = p.startNodeId JOIN ?_taxinodes n2 ON n2.id = p.endNodeId WHERE n1.typeId IN (?a) AND n2.typeId IN (?a)', array_keys($flightNodes), array_keys($flightNodes));

                foreach ($paths as $k => $path)
                {
                    foreach ($som['flightmaster'] as &$fm)
                    {
                        if ($fm['id'] != $path[0] && $fm['id'] != $path[1])
                            continue;

                        if ($fm['id'] == $path[0])
                            $fm['paths'][] = $flightNodes[$path[1]];

                        if ($fm['id'] == $path[1])
                            $fm['paths'][] = $flightNodes[$path[0]];

                        unset($paths[$k]);
                        break;
                    }
                }
            }

            // preselect bosses for raids/dungeons
            if (in_array($this->subject->getField('type'), [2, 3, 4, 5, 7, 8]))
                $som['instance'] = true;

            $this->map = array(
                'data' => ['parent' => 'mapper-generic', 'zone' => $this->typeId],
                'som'  => $som
            );
        }
        else
            $this->map = false;

        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );

    /*
        - associated with holiday?
     */

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: NPCs
        if ($cSpawns && !$creatureSpawns->error)
        {
            $tabData = array(
                'data' => array_values($creatureSpawns->getListviewData()),
                'note' => sprintf(Util::$filterResultString, '?npcs&filter=cr=6;crs='.$this->typeId.';crv=0')
            );

            if ($creatureSpawns->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tabData['_truncated'] = 1;

            $this->extendGlobalData($creatureSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = [CreatureList::$brickFile, $tabData];
        }

        // tab: Objects
        if ($oSpawns && !$objectSpawns->error)
        {
            $tabData = array(
                'data' => array_values($objectSpawns->getListviewData()),
                'note' => sprintf(Util::$filterResultString, '?objects&filter=cr=1;crs='.$this->typeId.';crv=0')
            );

            if ($objectSpawns->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $tabData['_truncated'] = 1;

            $this->extendGlobalData($objectSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = [GameObjectList::$brickFile, $tabData];
        }

        $quests = new QuestList(array(['zoneOrSort', $this->typeId]));
        if (!$quests->error)
        {
            $this->extendGlobalData($quests->getJSGlobals());
            foreach ($quests->getListviewData() as $id => $data)
            {
                if (!empty($quests->rewards[$id][Type::ITEM]))
                    $rewardsLV = array_merge($rewardsLV, array_keys($quests->rewards[$id][Type::ITEM]));

                if (!empty($quests->choices[$id][Type::ITEM]))
                    $rewardsLV = array_merge($rewardsLV, array_keys($quests->choices[$id][Type::ITEM]));

                $questsLV[$id] = $data;
            }
        }

        // tab: Quests [including data collected by SOM-routine]
        if ($questsLV)
        {
            $tabData = ['data' => array_values($questsLV)];

            foreach (Game::$questClasses as $parent => $children)
            {
                if (!in_array($this->typeId, $children))
                    continue;

                $tabData['note'] = '$$WH.sprintf(LANG.lvnote_zonequests, '.$parent.', '.$this->typeId.',"'.$this->subject->getField('name', true).'", '.$this->typeId.')';
                break;
            }

            $this->lvTabs[] = [QuestList::$brickFile, $tabData];
        }

        // tab: item-quest starter
        // select every quest starter, that is a drop
        $questStartItem = DB::Aowow()->select('
            SELECT qse.typeId AS ARRAY_KEY, moreType, moreTypeId, moreZoneId
            FROM   ?_quests_startend qse JOIN ?_source src ON src.type = qse.type AND src.typeId = qse.typeId
            WHERE  src.src2 IS NOT NULL AND qse.type = ?d AND (moreZoneId = ?d OR (moreType = ?d AND moreTypeId IN (?a)) OR (moreType = ?d AND moreTypeId IN (?a)))',
            Type::ITEM,   $this->typeId,
            Type::NPC,    array_unique(array_column($cSpawns, 'typeId')) ?: [0],
            Type::OBJECT, array_unique(array_column($oSpawns, 'typeId')) ?: [0]
        );

        if ($questStartItem)
        {
            $qsiList = new ItemList(array(['id', array_keys($questStartItem)]));
            if (!$qsiList->error)
            {
                $this->lvTabs[] = [ItemList::$brickFile, array(
                    'data' => array_values($qsiList->getListviewData()),
                    'name' => '$LANG.tab_startsquest',
                    'id'   => 'starts-quest'
                )];

                $this->extendGlobalData($qsiList->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: Quest Rewards [ids collected by SOM-routine]
        if ($rewardsLV)
        {
            $rewards = new ItemList(array(['id', array_unique($rewardsLV)]));
            if (!$rewards->error)
            {
                $this->lvTabs[] = [ItemList::$brickFile, array(
                    'data' => array_values($rewards->getListviewData()),
                    'name' => '$LANG.tab_questrewards',
                    'id'   => 'quest-rewards',
                    'note' => sprintf(Util::$filterResultString, '?items&filter=cr=126;crs='.$this->typeId.';crv=0')
                )];

                $this->extendGlobalData($rewards->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: achievements

        // tab: fished in zone
        $fish = new Loot();
        if ($fish->getByContainer(LOOT_FISHING, $this->typeId))
        {
            $this->extendGlobalData($fish->jsGlobals);
            $xCols = array_merge(['$Listview.extraCols.percent'], $fish->extraCols);

            foreach ($fish->iterate() as $lv)
            {
                if (!$lv['quest'])
                    continue;

                $xCols = array_merge($xCols, ['$Listview.extraCols.condition']);

                $reqQuest[$lv['id']] = 0;

                $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
            }

            $note = '';
            if ($skill = DB::World()->selectCell('SELECT `skill` FROM skill_fishing_base_level WHERE `entry` = ?d', $this->typeId))
                $note = sprintf(Util::$lvTabNoteString, Lang::zone('fishingSkill'), Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_FISHING, $skill), Lang::FMT_HTML));
            else if ($parentArea && ($skill = DB::World()->selectCell('SELECT `skill` FROM skill_fishing_base_level WHERE `entry` = ?d', $parentArea)))
                $note = sprintf(Util::$lvTabNoteString, Lang::zone('fishingSkill'), Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_FISHING, $skill), Lang::FMT_HTML));

            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data'       => array_values($fish->getResult()),
                'name'       => '$LANG.tab_fishing',
                'id'         => 'fishing',
                'extraCols'  => array_unique($xCols),
                'hiddenCols' => ['side'],
                'note'       => $note
            )];
        }

        // tab: spells
        if ($saData = DB::World()->select('SELECT * FROM spell_area WHERE area = ?d', $this->typeId))
        {
            $spells = new SpellList(array(['id', array_column($saData, 'spell')]));
            if (!$spells->error)
            {
                $lvSpells = $spells->getListviewData();
                $this->extendGlobalData($spells->getJSGlobals());

                $extra = false;
                foreach ($saData as $a)
                {
                    if (empty($lvSpells[$a['spell']]))
                        continue;

                    $condition = [];
                    if ($a['aura_spell'])
                    {
                        $this->extendGlobalIds(Type::SPELL, abs($a['aura_spell']));
                        $condition[0][$this->typeId][] = [[$a['aura_spell'] >  0 ? CND_AURA : -CND_AURA, abs($a['aura_spell'])]];
                    }

                    if ($a['quest_start'])                  // status for quests needs work
                    {
                        $this->extendGlobalIds(Type::QUEST, $a['quest_start']);
                        $group = [];
                        for ($i = 0; $i < 7; $i++)
                        {
                            if (!($a['quest_start_status'] & (1 << $i)))
                                continue;

                            if ($i == 0)
                                $group[] = [CND_QUEST_NONE, $a['quest_start']];
                            else if ($i == 1)
                                $group[] = [CND_QUEST_COMPLETE, $a['quest_start']];
                            else if ($i == 3)
                                $group[] = [CND_QUESTTAKEN, $a['quest_start']];
                            else if ($i == 6)
                                $group[] = [CND_QUESTREWARDED, $a['quest_start']];
                        }

                        if ($group)
                            $condition[0][$this->typeId][] = $group;
                    }

                    if ($a['quest_end'] && $a['quest_end'] != $a['quest_start'])
                    {
                        $this->extendGlobalIds(Type::QUEST, $a['quest_end']);
                        $group = [];
                        for ($i = 0; $i < 7; $i++)
                        {
                            if (!($a['quest_end_status'] & (1 << $i)))
                                continue;

                            if ($i == 0)
                                $group[] = [-CND_QUEST_NONE, $a['quest_end']];
                            else if ($i == 1)
                                $group[] = [-CND_QUEST_COMPLETE, $a['quest_end']];
                            else if ($i == 3)
                                $group[] = [-CND_QUESTTAKEN, $a['quest_end']];
                            else if ($i == 6)
                                $group[] = [-CND_QUESTREWARDED, $a['quest_end']];
                        }

                        if ($group)
                            $condition[0][$this->typeId][] = $group;
                    }

                    if ($a['racemask'])
                    {
                        $foo = [];
                        for ($i = 0; $i < 11; $i++)
                            if ($a['racemask'] & (1 << $i))
                                $foo[] = $i + 1;

                        $this->extendGlobalIds(Type::CHR_RACE, ...$foo);
                        $condition[0][$this->typeId][] = [[CND_RACE, $a['racemask']]];
                    }

                    if ($a['gender'] != 2)                  // 2: both
                        $condition[0][$this->typeId][] = [[CND_GENDER, $a['gender'] + 1]];

                    if ($condition)
                    {
                        $extra = true;
                        $lvSpells[$a['spell']] = array_merge($lvSpells[$a['spell']], ['condition' => $condition]);
                    }
                }

                $tabData = array(
                    'data'       => array_values($lvSpells),
                    'hiddenCols' => ['skill']
                );

                if ($extra)
                    $tabData['extraCols'] = ['$Listview.extraCols.condition'];

                $this->lvTabs[] = [SpellList::$brickFile, $tabData];
            }
        }

        // tab: subzones
        $subZones = new ZoneList(array(['parentArea', $this->typeId]));
        if (!$subZones->error)
        {
            $this->lvTabs[] = [ZoneList::$brickFile, array(
                'data'       => array_values($subZones->getListviewData()),
                'name'       => '$LANG.tab_zones',
                'id'         => 'subzones',
                'hiddenCols' => ['territory', 'instancetype']
            )];

            $this->extendGlobalData($subZones->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: sound (including subzones; excluding parents)
        $areaIds = [];
        if (!$subZones->error)
            $areaIds = $subZones->getFoundIDs();

        $areaIds[] = $this->typeId;

        $soundIds  = [];
        $zoneMusic = DB::Aowow()->select('
            SELECT
                x.soundId AS ARRAY_KEY, x.soundId, x.worldStateId, x.worldStateValue, x.type
            FROM (
                SELECT ambienceDay   AS soundId, worldStateId, worldStateValue, 1 AS `type` FROM ?_zones_sounds WHERE id IN (?a) AND ambienceDay   > 0 UNION
                SELECT ambienceNight AS soundId, worldStateId, worldStateValue, 1 AS `type` FROM ?_zones_sounds WHERE id IN (?a) AND ambienceNight > 0 UNION
                SELECT musicDay      AS soundId, worldStateId, worldStateValue, 2 AS `type` FROM ?_zones_sounds WHERE id IN (?a) AND musicDay      > 0 UNION
                SELECT musicNight    AS soundId, worldStateId, worldStateValue, 2 AS `type` FROM ?_zones_sounds WHERE id IN (?a) AND musicNight    > 0 UNION
                SELECT intro         AS soundId, worldStateId, worldStateValue, 3 AS `type` FROM ?_zones_sounds WHERE id IN (?a) AND intro         > 0
            ) x
            GROUP BY
                x.soundId, x.worldStateId, x.worldStateValue
       ', $areaIds, $areaIds, $areaIds, $areaIds, $areaIds);

        if ($sSpawns = DB::Aowow()->selectCol('SELECT typeId FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, Type::SOUND))
            $soundIds = array_merge($soundIds, $sSpawns);

        if ($zoneMusic)
            $soundIds = array_merge($soundIds, array_column($zoneMusic, 'soundId'));

        if ($soundIds)
        {
            $music = new SoundList(array(['id', array_unique($soundIds)]));
            if (!$music->error)
            {
                // tab
                $data    = $music->getListviewData();
                $tabData = [];

                if (array_filter(array_column($zoneMusic, 'worldStateId')))
                {
                    $tabData['extraCols']  = ['$Listview.extraCols.condition'];

                    foreach ($soundIds as $sId)
                        if (!empty($zoneMusic[$sId]['worldStateId']))
                            $data[$sId]['condition'][0][$this->typeId][] = [[CND_WORLD_STATE, $zoneMusic[$sId]['worldStateId'], $zoneMusic[$sId]['worldStateValue']]];
                }

                $tabData['data'] = array_values($data);

                $this->lvTabs[] = [SoundList::$brickFile, $tabData];

                $this->extendGlobalData($music->getJSGlobals(GLOBALINFO_SELF));

                // audio controls
                // ambience
                if ($sounds = array_filter($zoneMusic, function ($x) { return $x['type'] == 1; } ))
                    foreach ($sounds as $sId => $_)
                        if (!empty($data[$sId]['files']))
                            foreach ($data[$sId]['files'] as $f)
                                $this->zoneMusic['ambience'][] = $f;

                // music
                if ($sounds = array_filter($zoneMusic, function ($x) { return $x['type'] == 2; } ))
                    foreach ($sounds as $sId => $_)
                        if (!empty($data[$sId]['files']))
                            foreach ($data[$sId]['files'] as $f)
                                $this->zoneMusic['music'][] = $f;

                // intro
                if ($sounds = array_filter($zoneMusic, function ($x) { return $x['type'] == 3; } ))
                    foreach ($sounds as $sId => $_)
                        if (!empty($data[$sId]['files']))
                            foreach ($data[$sId]['files'] as $f)
                                $this->zoneMusic['intro'][] = $f;

            }
        }
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('category');

        if (in_array($this->subject->getField('category'), [2, 3]))
            $this->path[] = $this->subject->getField('expansion');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('zone')));
    }

}

?>
