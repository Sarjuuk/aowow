<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZoneBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'zone';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 6];

    protected  array  $dataLoader = ['zones'];
    protected  array  $scripts    = [[SC_JS_FILE, 'js/ShowOnMap.js']];

    public  int    $type      = Type::ZONE;
    public  int    $typeId    = 0;
    public  array  $zoneMusic = [];
    public ?string $expansion = null;

    private ZoneList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new ZoneList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('zone'), Lang::zone('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_parentArea = $this->subject->getField('parentArea');
        $_type       = $this->subject->getField('type');


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('category');

        if (in_array($this->subject->getField('category'), [MAP_TYPE_DUNGEON, MAP_TYPE_RAID]))
            $this->breadcrumb[] = $this->subject->getField('expansion');


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('zone')));


        /***********/
        /* Infobox */
        /***********/

        $quickFactsRows = DB::Aowow()->selectCol('SELECT `orderIdx` AS  ARRAY_KEY, `row` FROM ?_quickfacts WHERE `type` = ?d AND `typeId` = ?d ORDER BY `orderIdx` ASC', $this->type, $this->typeId);
        $quickFactsRows = preg_replace_callback('/\|L:(\w+)((:\w+)+)\|/i', function ($m)
        {
            [, $grp, $args] = $m;
            $args = array_filter(explode(':', $args), fn($x) => $x != '');

            return Lang::$grp(...$args);
        }, $quickFactsRows);

        foreach ($quickFactsRows as $er)
            $this->extendGlobalData(Markup::parseTags($er));

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        if ($topRows = array_filter($quickFactsRows, fn($x) => $x < 0, ARRAY_FILTER_USE_KEY))
            $infobox = array_merge($infobox, $topRows);

        // City
        if ($this->subject->getField('flags') & AREA_FLAG_SLAVE_CAPITAL && !$_parentArea)
            $infobox[] = Lang::zone('city');

        // Auto repop
        if ($this->subject->getField('flags') & AREA_FLAG_NEED_FLY && !$_parentArea)
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
                $buff = Lang::zone('reqLevels', [$_, $__]);
            else
                $buff = Lang::main('_reqLevel').Lang::main('colon').$_;

            $infobox[] = $buff;
        }

        // Territory
        $faction = $this->subject->getField('faction');
        $wrap    = match ($faction)
        {
            TEAM_ALLIANCE => '[span class=icon-alliance]%s[/span]',
            TEAM_HORDE    => '[span class=icon-horde]%s[/span]',
            4, 5          => '[span class=icon-ffa]%s[/span]',
            default       => '%s'
        };

        $infobox[] = Lang::zone('territory').sprintf($wrap, Lang::zone('territories', $faction));

        // Instance Type
        $infobox[] = Lang::zone('instanceType').'[span class=icon-instance'.$this->subject->getField('type').']'.Lang::zone('instanceTypes', $this->subject->getField('type')).'[/span]';

        // Heroic mode
        if ($_ = $this->subject->getField('levelHeroic'))
            $infobox[] = '[icon preset=heroic]'.Lang::zone('hcAvailable', [$_]).'[/icon]';

        // number of players
        if ($_ = $this->subject->getField('maxPlayer'))
        {
            if (in_array($this->subject->getField('category'), [6, 9]))
                $infobox[] = Lang::zone('numPlayersVs', [$_]);
            else
                $infobox[] = Lang::zone('numPlayers', [$_ == -2 ? '10/25' : $_]);
        }

        // Instances
        if ($_ = DB::Aowow()->selectCol('SELECT `typeId` FROM ?_spawns WHERE `type`= ?d AND `areaId` = ?d ', Type::ZONE, $this->typeId))
        {
            $this->extendGlobalIds(Type::ZONE, ...$_);
            $infobox[] = Lang::maps('Instances').Lang::main('colon').Lang::concat($_, Lang::CONCAT_NONE, fn($x) => "\n[zone=".$x."]");
        }

        // start area
        if ($_ = DB::Aowow()->selectCol('SELECT `id` FROM ?_races WHERE `startAreaId` = ?d', $this->typeId))
        {
            $this->extendGlobalIds(Type::CHR_RACE, ...$_);
            $infobox[] = Lang::concat($_, Lang::CONCAT_NONE, fn($x) => '[race='.$x.']').' '.Lang::race('startZone');
        }

        parent::generate(); // calls applyGlobals .. probably too early here, but addMoveLocationMenu requires PageTemplate to be initialized

        // location (if instance)
        if ($pa = DB::Aowow()->selectRow('SELECT `areaId`, `posX`, `posY`, `floor` FROM ?_spawns WHERE `type`= ?d AND `typeId` = ?d ', Type::ZONE, $this->typeId))
        {
            $this->addMoveLocationMenu($pa['areaId'], $pa['floor']);

            $pins = str_pad($pa['posX'] * 10, 3, '0', STR_PAD_LEFT) . str_pad($pa['posY'] * 10, 3, '0', STR_PAD_LEFT);
            $infobox[] = Lang::zone('location').'[lightbox=map zone='.$pa['areaId'].' '.($pa['floor'] > 1 ? 'floor='.--$pa['floor'] : '').' pins='.$pins.']'.ZoneList::getName($pa['areaId']).'[/lightbox]';
        }

        // Attunement Quest/Achievements & Keys
        if ($attmnt = $this->subject->getField('attunes'))
        {
            foreach ($attmnt as $type => $ids)
            {
                $this->extendGlobalIds($type, ...array_map('abs', $ids));
                foreach ($ids as $id)
                {
                    if ($type == Type::ITEM)
                        $infobox[] = Lang::zone('key', (int)($id < 0)).'[item='.abs($id).']';
                    else
                        $infobox[] = Lang::zone('attunement', (int)($id < 0)).'['.Type::getFileString($type).'='.abs($id).']';
                }
            }
        }

        // id
        $infobox[] = Lang::zone('id') . $this->typeId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($botRows = array_filter($quickFactsRows, fn($x) => $x > 0, ARRAY_FILTER_USE_KEY))
            $infobox = array_merge($infobox, $botRows);

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $addToSOM = function (string $what, string $group, array $entry) use (&$som) : void
        {
            // entry always contains: type, id, name, level, coords[]
            if (!isset($som[$what][$group]))                // not found yet
                $som[$what][$group][] = $entry;
            else                                            // found .. something..
            {
                // check for identical floors
                foreach ($som[$what][$group] as &$byFloor)
                {
                    if ($byFloor['level'] != $entry['level'])
                        continue;

                    // found existing floor, ammending coords
                    $byFloor['coords'][] = $entry['coords'][0];
                    return;
                }

                // floor not used yet, create it
                $som[$what][$group][] = $entry;
            }
        };

        if ($_parentArea)
        {
            $this->extraText = new Markup(Lang::zone('zonePartOf', [$_parentArea]), ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');
            $this->extendGlobalIds(Type::ZONE, $_parentArea);
        }

        // we cannot fetch spawns via lists. lists are grouped by entry
        $oSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE `areaId` = ?d AND `type` = ?d AND `posX` > 0 AND `posY` > 0', $this->typeId, Type::OBJECT);
        $cSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE `areaId` = ?d AND `type` = ?d AND `posX` > 0 AND `posY` > 0', $this->typeId, Type::NPC);
        $aSpawns = User::isInGroup(U_GROUP_STAFF) ? DB::Aowow()->select('SELECT * FROM ?_spawns WHERE `areaId` = ?d AND `type` = ?d AND `posX` > 0 AND `posY` > 0', $this->typeId, Type::AREATRIGGER) : [];

        $conditions = [['s.areaId', $this->typeId]];
        if (!User::isInGroup(U_GROUP_STAFF))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        $objectSpawns   = new GameObjectList($conditions, ['calcTotal' => true]);
        $creatureSpawns = new CreatureList($conditions, ['calcTotal' => true]);
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
        $mapFilePath = 'static/images/wow/maps/%s/normal/%d%s.jpg';
        $options     = array(
            [Lang::getLocale()->json(), ''],                // default case
            [Lang::getLocale()->json(), '-1'],              // try multifloor
            ['enus', ''],                                   // try english fallback
            ['enus', '-1']                                  // try english fallback, multifloor
        );
        $hasMap = false;
        foreach ($options as [$lang, $floor])
        {
            if (!file_exists(sprintf($mapFilePath, $lang, $this->typeId, $floor)))
                continue;

            $hasMap = true;
            break;
        }

        if ($hasMap)
        {
            $som = [];
            foreach ($oSpawns as $spawn)
            {
                $tpl = $objectSpawns->getEntry($spawn['typeId']);
                if (!$tpl)
                    continue;

                $n = Util::localizedString($tpl, 'name');

                $what = match ((int)$tpl['typeCat'])
                {
                    -3      => 'herb',
                    -4      => 'vein',
                     9      => 'book',
                    25      => 'pool',
                     0      => $tpl['type'] == 19 ? 'mail' : '',
                    -6      => $tpl['spellFocusId'] == 1 ? 'anvil' : ($tpl['spellFocusId'] == 3 ? 'forge' : ''),
                    default => ''
                };

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
                    {
                        $blob['side'] = (($tpl['A'] < 0 ? 0 : SIDE_ALLIANCE) | ($tpl['H'] < 0 ? 0 : SIDE_HORDE));
                        $addToSOM($what, $tpl['id'], $blob);
                    }
                    else
                        $addToSOM($what, $n, $blob);
                }

                if ($tpl['startsQuests'])
                {
                        $started = new QuestList(array(['qse.method', 1, '&'], ['qse.type', Type::OBJECT], ['qse.typeId', $tpl['id']]));
                        if ($started->error)
                            continue;

                        // store data for misc tabs
                        foreach ($started->getListviewData() as $id => $data)
                        {
                            if ($started->getField('questSortId') > 0 && !in_array($started->getField('questSortId'), $relQuestZOS))
                                continue;

                            if (!empty($started->rewards[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->rewards[$id][Type::ITEM]));

                            if (!empty($started->choices[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->choices[$id][Type::ITEM]));

                            $questsLV[$id] = $data;
                        }

                        $this->extendGlobalData($started->getJSGlobals());

                        if (($tpl['A'] != -1) && ($_ = $started->getSOMData(SIDE_ALLIANCE)))
                            $addToSOM('alliancequests', $n, array(
                                'coords' => [[$spawn['posX'], $spawn['posY']]],
                                'level'  => $spawn['floor'],
                                'name'   => $n,
                                'type'   => Type::OBJECT,
                                'id'     => $tpl['id'],
                                'side'   => (($tpl['A'] < 0 ? 0 : SIDE_ALLIANCE) | ($tpl['H'] < 0 ? 0 : SIDE_HORDE)),
                                'quests' => array_values($_)
                            ));

                        if (($tpl['H'] != -1) && ($_ = $started->getSOMData(SIDE_HORDE)))
                            $addToSOM('hordequests', $n, array(
                                'coords' => [[$spawn['posX'], $spawn['posY']]],
                                'level'  => $spawn['floor'],
                                'name'   => $n,
                                'type'   => Type::OBJECT,
                                'id'     => $tpl['id'],
                                'side'   => (($tpl['A'] < 0 ? 0 : SIDE_ALLIANCE) | ($tpl['H'] < 0 ? 0 : SIDE_HORDE)),
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

                $flagsMap = array(
                    NPC_FLAG_REPAIRER        => 'repair',
                    NPC_FLAG_AUCTIONEER      => 'auctioneer',
                    NPC_FLAG_BANKER          => 'banker',
                    NPC_FLAG_BATTLEMASTER    => 'battlemaster',
                    NPC_FLAG_INNKEEPER       => 'innkeeper',
                    NPC_FLAG_TRAINER         => 'trainer',
                    NPC_FLAG_VENDOR          => 'vendor',
                    NPC_FLAG_FLIGHT_MASTER   => 'flightmaster',
                    NPC_FLAG_STABLE_MASTER   => 'stablemaster',
                    NPC_FLAG_GUILD_MASTER    => 'guildmaster',
                    NPC_FLAG_SPIRIT_HEALER |
                    NPC_FLAG_SPIRIT_GUIDE    => 'spirithealer',
                    0                        => ''          // set 'unused' if no match
                );

                if ($creatureSpawns->isBoss())
                    $what = 'boss';
                else if ($tpl['rank'] == NPC_RANK_RARE_ELITE || $tpl['rank'] == NPC_RANK_RARE)
                    $what = 'rare';
                else
                    foreach ($flagsMap as $flag => $what)
                        if ($tpl['npcflag'] & $flag)
                            break;

                if ($what == 'flightmaster')
                    $flightNodes[$tpl['id']] = [$spawn['posX'], $spawn['posY']];

                if ($what)
                    $addToSOM($what, $n, array(
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
                            if ($started->getField('questSortId') > 0 && !in_array($started->getField('questSortId'), $relQuestZOS))
                                continue;

                            if (!empty($started->rewards[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->rewards[$id][Type::ITEM]));

                            if (!empty($started->choices[$id][Type::ITEM]))
                                $rewardsLV = array_merge($rewardsLV, array_keys($started->choices[$id][Type::ITEM]));

                            $questsLV[$id] = $data;
                        }

                        $this->extendGlobalData($started->getJSGlobals());

                        if (($tpl['A'] != -1) && ($_ = $started->getSOMData(SIDE_ALLIANCE)))
                            $addToSOM('alliancequests', $n, array(
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
                            $addToSOM('hordequests', $n, array(
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
                if ($spawn['guid'] < 0)                     // skip teleporter endpoints
                    continue;

                $tpl = $atSpawns->getEntry($spawn['typeId']);
                if (!$tpl)
                    continue;

                $n = Util::localizedString($tpl, 'name', true, true);
                $addToSOM('areatrigger', $n, array(
                    'coords'        => [[$spawn['posX'], $spawn['posY']]],
                    'level'         => $spawn['floor'],
                    'name'          => $n,
                    'type'          => Type::AREATRIGGER,
                    'id'            => $spawn['typeId'],
                    'description'   => Lang::game('type').Lang::areatrigger('types', $tpl['type'])
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
                    $n1 = (int)$a['reactalliance'] == $a['reacthorde'];
                    $n2 = (int)$b['reactalliance'] == $b['reacthorde'];

                    return $n1 <=> $n2;
                });

                $paths = DB::Aowow()->select('SELECT n1.`typeId` AS "0", n2.`typeId` AS "1" FROM ?_taxipath p JOIN ?_taxinodes n1 ON n1.`id` = p.`startNodeId` JOIN ?_taxinodes n2 ON n2.`id` = p.`endNodeId` WHERE n1.`typeId` IN (?a) AND n2.`typeId` IN (?a)', array_keys($flightNodes), array_keys($flightNodes));

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
            if (in_array($_type, [MAP_TYPE_DUNGEON, MAP_TYPE_RAID, MAP_TYPE_BATTLEGROUND, MAP_TYPE_DUNGEON_HC, MAP_TYPE_MMODE_RAID, MAP_TYPE_MMODE_RAID_HC]))
                $som['instance'] = true;

            $this->map = array(
                array(                                      // Mapper
                    'parent'   => 'mapper-generic',
                    'zone'     => $this->typeId,
                    'zoneLink' => false
                ),
                null,                                       // mapperData
                $som,                                       // ShowOnMap
                null                                        // foundIn
            );
        }

        $this->expansion  = Util::$expansionString[$this->subject->getField('expansion')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId]
        );


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // tab: drops
        if (in_array($this->subject->getField('category'), [MAP_TYPE_DUNGEON, MAP_TYPE_RAID]))
        {
            // Issue 1 - if the bosses drop items that are also sold by vendors moreZoneId will be 0 as vendor location and boss location are likely in conflict with each other
            // Issue 2 - if the boss/chest isn't spawned the loot will not show up
            $items   = new ItemList(array(['src.moreZoneId', $this->typeId], ['src.src2', 0, '>'], ['quality', ITEM_QUALITY_UNCOMMON, '>=']), ['calcTotal' => true]);
            $data    = $items->getListviewData();
            $subTabs = false;
            foreach ($items->iterate() as $id => $__)
            {
                $src = $items->getRawSource(SRC_DROP);
                $map = ($items->getField('moreMask') ?: 0) & (SRC_FLAG_DUNGEON_DROP | SRC_FLAG_RAID_DROP);
                if (!$src || !$map)
                    continue;

                $subTabs = true;

                if ($map & SRC_FLAG_RAID_DROP)
                    $mode = ($src[0] << 3);
                else
                    $mode = ($src[0] & 0x1 ? 0x2 : 0) | ($src[0] & 0x2 ? 0x1 : 0);

                $data[$id] += ['modes' => ['mode' => $mode]];
            }

            $tabData = array(
                'data'            => $data,
                'id'              => 'drops',
                'name'            => '$LANG.tab_drops',
                'extraCols'       => $subTabs ? ['$Listview.extraCols.mode'] : null,
                'computeDataFunc' => '$Listview.funcBox.initLootTable',
                'onAfterCreate'   => $subTabs ? '$Listview.funcBox.addModeIndicator' : null
            );

            if (!is_null(ItemListFilter::getCriteriaIndex(16, $this->typeId)))
                $tabData['note'] = sprintf(Util::$filterResultString, '?items&filter=cr=16;crs='.$this->typeId.';crv=0');

            $this->extendGlobalData($items->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));
        }

        // tab: npcs
        if ($cSpawns && !$creatureSpawns->error)
        {
            $tabData = ['data' => $creatureSpawns->getListviewData()];

            if (!is_null(CreatureListFilter::getCriteriaIndex(6, $this->typeId)))
                $tabData['note'] = sprintf(Util::$filterResultString, '?npcs&filter=cr=6;crs='.$this->typeId.';crv=0');

            if ($creatureSpawns->getMatches() > Listview::DEFAULT_SIZE)
                $tabData['_truncated'] = 1;

            $this->extendGlobalData($creatureSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs->addListviewTab(new Listview($tabData, CreatureList::$brickFile));
        }

        // tab: objects
        if ($oSpawns && !$objectSpawns->error)
        {
            $tabData = ['data' => $objectSpawns->getListviewData()];

            if (!is_null(GameObjectListFilter::getCriteriaIndex(1, $this->typeId)))
                $tabData['note'] = sprintf(Util::$filterResultString, '?objects&filter=cr=1;crs='.$this->typeId.';crv=0');

            if ($objectSpawns->getMatches() > Listview::DEFAULT_SIZE)
                $tabData['_truncated'] = 1;

            $this->extendGlobalData($objectSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs->addListviewTab(new Listview($tabData, GameObjectList::$brickFile));
        }

        $quests = new QuestList(array(['questSortId', $this->typeId]));
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

        // tab: quests [including data collected by SOM-routine]
        if ($questsLV)
        {
            $tabData = ['data' => $questsLV];

            foreach (Game::QUEST_CLASSES as $parent => $children)
            {
                if (!in_array($this->typeId, $children))
                    continue;

                if (!is_null(ItemListFilter::getCriteriaIndex(126, $this->typeId)))
                    $tabData['note'] = '$$WH.sprintf(LANG.lvnote_zonequests, '.$parent.', '.$this->typeId.',"'.$this->subject->getField('name', true).'", '.$this->typeId.')';
                else
                    $tabData['note'] = '$$WH.sprintf(LANG.lvnote_questsind, '.$parent.', '.$this->typeId.',"'.$this->subject->getField('name', true).'")';
                break;
            }

            $this->lvTabs->addListviewTab(new Listview($tabData, QuestList::$brickFile));
        }

        // tab: starts-quest
        // select every quest starter, that is a drop
        $questStartItem = DB::Aowow()->select(
           'SELECT qse.`typeId` AS ARRAY_KEY, `moreType`, `moreTypeId`, `moreZoneId`
            FROM   ?_quests_startend qse JOIN ?_source src ON src.`type` = qse.`type` AND src.`typeId` = qse.`typeId`
            WHERE  src.`src2` IS NOT NULL AND qse.`type` = ?d AND (`moreZoneId` = ?d OR (`moreType` = ?d AND `moreTypeId` IN (?a)) OR (`moreType` = ?d AND `moreTypeId` IN (?a)))',
            Type::ITEM,   $this->typeId,
            Type::NPC,    array_unique(array_column($cSpawns, 'typeId')) ?: [0],
            Type::OBJECT, array_unique(array_column($oSpawns, 'typeId')) ?: [0]
        );

        if ($questStartItem)
        {
            $qsiList = new ItemList(array(['id', array_keys($questStartItem)]));
            if (!$qsiList->error)
            {
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $qsiList->getListviewData(),
                    'name' => '$LANG.tab_startsquest',
                    'id'   => 'starts-quest'
                ), ItemList::$brickFile));

                $this->extendGlobalData($qsiList->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: quest-rewards [ids collected by SOM-routine]
        if ($rewardsLV)
        {
            $rewards = new ItemList(array(['id', array_unique($rewardsLV)]));
            if (!$rewards->error)
            {
                $note = null;
                if (!is_null(ItemListFilter::getCriteriaIndex(126, $this->typeId)))
                    $note = sprintf(Util::$filterResultString, '?items&filter=cr=126;crs='.$this->typeId.';crv=0');

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $rewards->getListviewData(),
                    'name' => '$LANG.tab_questrewards',
                    'id'   => 'quest-rewards',
                    'note' => $note
                ), ItemList::$brickFile));

                $this->extendGlobalData($rewards->getJSGlobals(GLOBALINFO_SELF));
            }
        }

        // tab: achievements

        // tab: criteria-of
        $conditions = array('OR',
            array(
                'AND',
                ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_QUESTS_IN_ZONE, ACHIEVEMENT_CRITERIA_TYPE_HONORABLE_KILL_AT_AREA]],
                ['ac.value1', $this->typeId]
            )
        );

        if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = ?d AND `value1` = ?d', ACHIEVEMENT_CRITERIA_DATA_TYPE_S_AREA, $this->typeId))
            $conditions[] = ['ac.id', $extraCrt];

        if ($this->subject->getField('category') != MAP_TYPE_ZONE)
        {
            $conditions[] = array (
                'AND',
                ['ac.type', [ACHIEVEMENT_CRITERIA_TYPE_WIN_BG,      ACHIEVEMENT_CRITERIA_TYPE_WIN_ARENA,
                             ACHIEVEMENT_CRITERIA_TYPE_PLAY_ARENA,  ACHIEVEMENT_CRITERIA_TYPE_COMPLETE_BATTLEGROUND,
                             ACHIEVEMENT_CRITERIA_TYPE_DEATH_AT_MAP]
                ],
                ['ac.value1', $this->subject->getField('mapId')]
            );

            if ($extraCrt = DB::World()->selectCol('SELECT `criteria_id` FROM achievement_criteria_data WHERE `type` = ?d AND `value1` = ?d', ACHIEVEMENT_CRITERIA_DATA_TYPE_MAP_ID, $this->subject->getField('mapId')))
                $conditions[] = ['ac.id', $extraCrt];

        }

        $crtOf = new AchievementList($conditions);
        if (!$crtOf->error)
        {
            $this->extendGlobalData($crtOf->getJSGlobals());

            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $crtOf->getListviewData(),
                'name' => '$LANG.tab_criteriaof',
                'id'   => 'criteria-of'
            ), AchievementList::$brickFile));
        }

        // tab: fishing
        $fish = new LootByContainer();
        if ($fish->getByContainer(Loot::FISHING, [$this->typeId]))
        {
            $this->extendGlobalData($fish->jsGlobals);
            $xCols = array_merge(['$Listview.extraCols.percent'], $fish->extraCols);

            $note = null;
            if ($skill = DB::World()->selectCell('SELECT `skill` FROM skill_fishing_base_level WHERE `entry` = ?d', $this->typeId))
                $note = sprintf(Util::$lvTabNoteString, Lang::zone('fishingSkill'), Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_FISHING, $skill), Lang::FMT_HTML));
            else if ($_parentArea && ($skill = DB::World()->selectCell('SELECT `skill` FROM skill_fishing_base_level WHERE `entry` = ?d', $_parentArea)))
                $note = sprintf(Util::$lvTabNoteString, Lang::zone('fishingSkill'), Lang::formatSkillBreakpoints(Game::getBreakpointsForSkill(SKILL_FISHING, $skill), Lang::FMT_HTML));

            $this->lvTabs->addListviewTab(new Listview(array(
                'data'            => $fish->getResult(),
                'name'            => '$LANG.tab_fishing',
                'id'              => 'fishing',
                'extraCols'       => array_unique($xCols),
                'hiddenCols'      => ['side'],
                'note'            => $note,
                'computeDataFunc' => '$Listview.funcBox.initLootTable'
            ), ItemList::$brickFile));
        }

        // tab: spells
        if ($saData = DB::World()->select('SELECT * FROM spell_area WHERE `area` = ?d', $this->typeId))
        {
            $spells = new SpellList(array(['id', array_column($saData, 'spell')]));
            if (!$spells->error)
            {
                $lvSpells = $spells->getListviewData();
                $this->extendGlobalData($spells->getJSGlobals());

                $cnd = new Conditions();
                foreach ($saData as $a)
                {
                    if (empty($lvSpells[$a['spell']]))
                        continue;

                    if ($a['aura_spell'])
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $a['spell'], [$a['aura_spell'] >  0 ? Conditions::AURA : -Conditions::AURA, abs($a['aura_spell'])]);

                    if ($a['quest_start'])                  // status for quests needs work
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $a['spell'], [Conditions::QUESTSTATE, $a['quest_start'], $a['quest_start_status']]);

                    if ($a['quest_end'] && $a['quest_end'] != $a['quest_start'])
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $a['spell'], [Conditions::QUESTSTATE, $a['quest_end'], $a['quest_end_status']]);

                    if ($a['racemask'])
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $a['spell'], [Conditions::CHR_RACE, $a['racemask']]);

                    if ($a['gender'] != 2)                  // 2: both
                        $cnd->addExternalCondition(Conditions::SRC_NONE, $a['spell'], [Conditions::GENDER, $a['gender']]);
                }

                if ($cnd->toListviewColumn($lvSpells, $extraCols))
                    $this->extendGlobalData($cnd->getJsGlobals());

                $this->lvTabs->addListviewTab(new Listview(array(
                    'data'       => $lvSpells,
                    'hiddenCols' => ['skill'],
                    'extraCols'  => $extraCols ?: null
                ), SpellList::$brickFile));
            }
        }

        // tab: subzones
        $subZones = new ZoneList(array(['parentArea', $this->typeId]));
        if (!$subZones->error)
        {
            $this->lvTabs->addListviewTab(new Listview(array(
                'data'       => $subZones->getListviewData(),
                'name'       => '$LANG.tab_zones',
                'id'         => 'subzones',
                'hiddenCols' => ['territory', 'instancetype']
            ), ZoneList::$brickFile));

            $this->extendGlobalData($subZones->getJSGlobals(GLOBALINFO_SELF));
        }

        // tab: sound (including subzones; excluding parents)
        $areaIds = [];
        if (!$subZones->error)
            $areaIds = $subZones->getFoundIDs();

        $areaIds[] = $this->typeId;

        $soundIds  = [];
        $zoneMusic = DB::Aowow()->select(
           'SELECT   x.`soundId` AS ARRAY_KEY, x.`soundId`, x.`worldStateId`, x.`worldStateValue`, x.`type`
            FROM    (SELECT `ambienceDay`   AS "soundId", `worldStateId`, `worldStateValue`, 1 AS "type" FROM ?_zones_sounds WHERE `id` IN (?a) AND `ambienceDay`   > 0 UNION
                     SELECT `ambienceNight` AS "soundId", `worldStateId`, `worldStateValue`, 1 AS "type" FROM ?_zones_sounds WHERE `id` IN (?a) AND `ambienceNight` > 0 UNION
                     SELECT `musicDay`      AS "soundId", `worldStateId`, `worldStateValue`, 2 AS "type" FROM ?_zones_sounds WHERE `id` IN (?a) AND `musicDay`      > 0 UNION
                     SELECT `musicNight`    AS "soundId", `worldStateId`, `worldStateValue`, 2 AS "type" FROM ?_zones_sounds WHERE `id` IN (?a) AND `musicNight`    > 0 UNION
                     SELECT `intro`         AS "soundId", `worldStateId`, `worldStateValue`, 3 AS "type" FROM ?_zones_sounds WHERE `id` IN (?a) AND `intro`         > 0) x
            GROUP BY x.soundId, x.worldStateId, x.worldStateValue',
            $areaIds, $areaIds, $areaIds, $areaIds, $areaIds
        );

        if ($sSpawns = DB::Aowow()->selectCol('SELECT `typeId` FROM ?_spawns WHERE `areaId` = ?d AND `type` = ?d', $this->typeId, Type::SOUND))
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
                    $tabData['extraCols'] = ['$Listview.extraCols.condition'];

                    foreach ($soundIds as $sId)
                        if (!empty($zoneMusic[$sId]['worldStateId']))
                            Conditions::extendListviewRow($data[$sId], Conditions::SRC_NONE, $this->typeId, [Conditions::WORLD_STATE, $zoneMusic[$sId]['worldStateId'], $zoneMusic[$sId]['worldStateValue']]);
                }

                $tabData['data'] = $data;

                $this->lvTabs->addListviewTab(new Listview($tabData, SoundList::$brickFile));

                $this->extendGlobalData($music->getJSGlobals(GLOBALINFO_SELF));

                $typeFilter = function(array $music, int $type) use ($data) : array
                {
                    $result = [];
                    foreach (array_filter($music, fn ($x) => $x['type'] == $type) as $sId => $_)
                        $result = array_merge($result, $data[$sId]['files'] ?? []);

                    return $result;
                };

                // audio controls (order how it appears on page)
                // [title, data, divID, options]
                if ($_ = $typeFilter($zoneMusic, 2))
                    $this->zoneMusic[] = [Lang::sound('music'), $_, 'zonemusic', (object)['loop' => true]];

                if ($_ = $typeFilter($zoneMusic, 3))
                    $this->zoneMusic[] = [Lang::sound('intro'), $_, 'zonemusicintro', (object)[]];

                if ($_ = $typeFilter($zoneMusic, 1))
                    $this->zoneMusic[] = [Lang::sound('ambience'), $_, 'soundambience', (object)['loop' => true]];
            }
        }

        // tab: condition-for
        $cnd = new Conditions();
        $cnd->getByCondition(Type::ZONE, $this->typeId)->prepare();
        if ($tab = $cnd->toListviewTab('condition-for', '$LANG.tab_condition_for'))
        {
            $this->extendGlobalData($cnd->getJsGlobals());
            $this->lvTabs->addDataTab(...$tab);
        }
    }

    private function addMoveLocationMenu(int $_parentArea, int $parentFloor) : void
    {
        // hide for non-staff
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            return;

        $worldPos = WorldPosition::getForGUID(Type::ZONE, -$this->typeId);
        if (!$worldPos)
            return;

        $menu = Util::buildPosFixMenu($worldPos[-$this->typeId]['mapId'], $worldPos[-$this->typeId]['posX'], $worldPos[-$this->typeId]['posY'], Type::ZONE, -$this->typeId, $_parentArea, $parentFloor);
        if (!$menu)
            return;

        $menu = [1002, 'Edit DB Entry', null, $menu];

        $this->addScript([SC_JS_STRING, '$(document).ready(function () { mn_staff.push('.Util::toJSON(array_values($menu)).'); });']);
    }
}

?>
