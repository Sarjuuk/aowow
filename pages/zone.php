<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 6: Zone     g_initPath()
//  tabId 0: Database g_initHeader()
class ZonePage extends GenericPage
{
    use detailPage;

    protected $path     = [0, 6];
    protected $tabId    = 0;
    protected $type     = TYPE_ZONE;
    protected $tpl      = 'detail-page-generic';
    protected $js       = ['Mapper.js', 'ShowOnMap.js'];
    protected $css      = array(
        ['path' => 'Mapper.css']
    );

    public function __construct($pageCall, $id)
    {
        $this->typeId = intVal($id);

        parent::__construct($pageCall, $id);

        $this->subject = new ZoneList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::$game['zone']);

        $this->name = $this->subject->getField('name', true);
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        /***********/
        /* Infobox */
        /***********/

        $infobox = [];

        // City
        if ($this->subject->getField('flags') & 0x200000 && !$this->subject->getField('parentArea'))
            $infobox[] = Lang::$zone['city'];

        // Level
        if ($_ = $this->subject->getField('levelMin'))
        {
            if ($_ < $this->subject->getField('levelMax'))
                $_ .= ' - '.$this->subject->getField('levelMax');

            $infobox[] = Lang::$game['level'].Lang::$main['colon'].$_;
        }

        // required Level
        // [li]Requires level 80[/li] || [li]Required levels: [tooltip=instancereqlevel_tip]80[/tooltip], [tooltip=lfgreqlevel_tip]80[/tooltip][/li]

        // Territory
        $_  = $this->subject->getField('faction');
        $__ = '%s';
        if ($_ == 0)
            $__ = '[span class=icon-alliance]%s[/span]';
        else if ($_ == 1)
            $__ = '[span class=icon-horde]%s[/span]';
        else if ($_ == 4)
            $__ = '[span class=icon-ffa]%s[/span]';

        $infobox[] = Lang::$zone['territory'].Lang::$main['colon'].sprintf($__, lang::$zone['territories'][$_]);

        // Instance Type
        $infobox[] = Lang::$zone['instanceType'].Lang::$main['colon'].'[span class=icon-instance'.$this->subject->getField('type').']'.Lang::$zone['instanceTypes'][$this->subject->getField('type')].'[/span]';

        // Heroic mode
        if ($_ = $this->subject->getField('levelHeroic'))
            $infobox[] = '[icon preset=heroic]'.sprintf(Lang::$zone['hcAvailable'], $_).'[/icon]';

        // number of players
        if ($_ = $this->subject->getField('maxPlayer'))
            $infobox[] = Lang::$zone['numPlayers'].Lang::$main['colon'].($_ == -2 ? '10/25' : $_);

        // attunement
        // [li]Attunement: [quest=24712][/li]

        // location (if instance)
        // [li]Location: [lightbox=map zone=210 pins=514883]Icecrown[/lightbox][/li]

        // Continent (if zone)
        // Continent: Outland

        // instances in this zone
        // Instance: The Slave Pens, The Steamvault, The Underbog, Serpentshrine Cavern

        // faction(s) / Reputation Hub / Raid Faction
        // [li]Raid faction: [faction=1156][/li] || [li]Factions: [faction=1156]/[faction=1156][/li]

        // final boss
        // [li]Final boss: [icon preset=boss][npc=37226][/icon][/li]


        /****************/
        /* Main Content */
        /****************/

        $oSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, TYPE_OBJECT);
        $conditions = [['id', array_column($oSpawns, 'typeId')]];
        if (!User::isInGroup(U_GROUP_STAFF))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($oSpawns)
            $objectSpawns = new GameObjectList($conditions);

        $cSpawns = DB::Aowow()->select('SELECT * FROM ?_spawns WHERE areaId = ?d AND type = ?d', $this->typeId, TYPE_NPC);

        $conditions = [['id', array_column($cSpawns, 'typeId')]];
        if (!User::isInGroup(U_GROUP_STAFF))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($cSpawns)
            $creatureSpawns = new CreatureList($conditions);

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
                    case -6:
                        if ($tpl['spellFocusId'] == 1)
                            $what = 'anvil';
                        else if ($tpl['spellFocusId'] == 3)
                            $what = 'forge';
                        else
                            continue 2;

                        break;
                    default:
                        continue 2;
                }

                if (!isset($som[$what][$n]))                    // not found yet
                {
                    $som[$what][$n][] = array(
                        'coords' => [[$spawn['posX'], $spawn['posY']]],
                        'level'  => $spawn['floor'],
                        'name'   => $n,
                        'type'   => TYPE_OBJECT,
                        'id'     => $tpl['id']
                    );
                }
                else                                            // found .. something..
                {
                    // check for identical floors
                    foreach ($som[$what][$n] as &$byFloor)
                    {
                        if ($byFloor['level'] != $spawn['floor'])
                            continue;

                        // found existing floor, ammending coords
                        $byFloor['coords'][] = [$spawn['posX'], $spawn['posY']];
                        continue 2;
                    }

                    // floor not used yet, create it
                    $som[$what][$n][] = array(
                        'coords' => [[$spawn['posX'], $spawn['posY']]],
                        'level'  => $spawn['floor'],
                        'name'   => $n,
                        'type'   => TYPE_OBJECT,
                        'id'     => $tpl['id']
                    );
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
                else if ($creatureSpawns->isBoss())             // ($tpl['rank'] == 3 || $tpl['cuFlags'] & NPC_CU_INSTANCE_BOSS)
                    $what = 'boss';
                else if ($tpl['rank'] == 2 || $tpl['rank'] == 4)
                    $what = 'rare';
                // questgiver (any type) ?
                else
                    continue;

                if (!isset($som[$what][$n]))                    // not found yet
                {
                    $som[$what][$n][] = array(
                        'coords'        => [[$spawn['posX'], $spawn['posY']]],
                        'level'         => $spawn['floor'],
                        'name'          => $n,
                        'type'          => TYPE_NPC,
                        'id'            => $tpl['id'],
                        'reacthorde'    => $tpl['H'] ?: 1,      // no neutral (0) setting
                        'reactalliance' => $tpl['A'] ?: 1,
                        'description'   => $sn
                    );
                }
                else                                            // found .. something..
                {
                    // check for identical floors
                    foreach ($som[$what][$n] as &$byFloor)
                    {
                        if ($byFloor['level'] != $spawn['floor'])
                            continue;

                        // found existing floor, ammending coords
                        $byFloor['coords'][] = [$spawn['posX'], $spawn['posY']];
                        continue 2;
                    }

                    // floor not used yet, create it
                    $som[$what][$n][] = array(
                        'coords'        => [[$spawn['posX'], $spawn['posY']]],
                        'level'         => $spawn['floor'],
                        'name'          => $n,
                        'type'          => TYPE_NPC,
                        'id'            => $tpl['id'],
                        'reacthorde'    => $tpl['H'] ?: 1,      // no neutral (0) setting
                        'reactalliance' => $tpl['A'] ?: 1,
                        'description'   => $sn
                    );
                }
            }

            // remove unwanted indizes
            foreach ($som as $what => &$dataz)
            {
                if (empty($som[$what]))
                    continue;

                foreach ($dataz as &$data)
                    $data = array_values($data);

                if (!in_array($what, ['vein', 'herb', 'rare']))
                    $dataz = array_column($dataz, 0);
            }

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
            if (in_array($this->subject->getField('areaType'), [1, 2]))
                $som['instance'] = true;

            /*
            var mapShower = new ShowOnMap(
                {
            1/2        alliancequests: [{ coords: [[71.8,46.4]], level: 0, name: 'Lord Ello Ebonlocke', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'The Embalmer\'s Revenge', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 263 },{ coords: [[73.6,46.8]], level: 0, name: 'Commander Althea Ebonlocke', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 18, name: 'Bones That Walk', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'The Hermit', series: 0, first: 0, category: 10, _category: 0 },{ level: 19, name: 'The Night Watch', series: 1, first: 1, category: 10, _category: 0 },{ level: 19, name: 'Wolves at Our Heels', series: 1, first: 0, category: 10, _category: 0 },{ level: 23, name: 'Mor\'Ladim', series: 1, first: 0, category: 10, _category: 0 },{ level: 23, name: 'The Daughter Who Lived', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 264 },{ coords: [[75.8,45.2]], level: 0, name: 'Madame Eva', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'Deliver the Thread', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Ghost Hair Thread', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'Mistmantle\'s Revenge', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 265 },{ coords: [[72.6,46.8]], level: 0, name: 'Clerk Daltry', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 21, name: 'In A Dark Corner', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'Roland\'s Doom', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'The Fate of Stalvan Mistmantle', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'The Stolen Letters', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 267 },{ coords: [[72.6,47.6]], level: 0, name: 'Sirra Von\'Indi', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 23, name: 'Morgan Ladimore', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 268 },{ coords: [[73.8,43.6]], level: 0, name: 'Chef Grual', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 18, name: 'Dusky Crab Cakes', series: 0, first: 0, category: 10, _category: 0 },{ level: 18, name: 'Seasoned Wolf Kabobs', series: 0, first: 0, category: 10, _category: 0 }], type: 1, id: 272 },{ coords: [[73.8,44.4]], level: 0, name: 'Tavernkeep Smitts', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'Gather Rot Blossoms', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Juice Delivery', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 273 },{ coords: [[79.4,47.2]], level: 0, name: 'Viktori Prism\'Antras', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'Classy Glass', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Look To The Stars', series: 1, first: 1, category: 10, _category: 0 }], type: 1, id: 276 },{ coords: [[18.6,58.2]], level: 0, name: 'Jitters', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 22, name: 'Bear In Mind', series: 1, first: 0, category: 10, _category: 0 },{ level: 22, name: 'The Jitters-Bugs', series: 1, first: 1, category: 10, _category: 0 }], type: 1, id: 288 },{ coords: [[87.4,35.4]], level: 0, name: 'Abercrombie', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'Ghoulish Effigy', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Note to the Mayor', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Ogre Thieves', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'Supplies from Darkshire', series: 1, first: 1, category: 10, _category: 0 },{ level: 19, name: 'Zombie Juice', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 289 },{ coords: [[81.8,59.2]], level: 0, name: 'Blind Mary', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 19, name: 'Return the Comb', series: 1, first: 0, category: 10, _category: 0 },{ level: 19, name: 'The Insane Ghoul', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 302 },{ coords: [[73.6,46.8]], level: 0, name: 'Watcher Ladimore', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 23, name: 'A Daughter\'s Love', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 576 },{ coords: [[77.6,44.4]], level: 0, name: 'Chicken', reacthorde: 0, reactalliance: 0, side: 3, quests: [{ level: 1, name: 'CLUCK!', series: 0, first: 0, category: 40, _category: 0 }], type: 1, id: 620 },{ coords: [[75.2,47.8]], level: 0, name: 'Calor', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 21, name: 'The Rotting Orchard', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'Vile and Tainted', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'Worgen in the Woods', series: 1, first: 1, category: 10, _category: 0 },{ level: 21, name: 'Worgen in the Woods', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 663 },{ coords: [[45,66.8]], level: 0, name: 'Watcher Dodds', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 20, name: 'Vulgar Vul\'Gol', series: 0, first: 0, category: 10, _category: 0 }], type: 1, id: 888 },{ coords: [[73.6,53.8]], level: 0, name: 'Fire Eater', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 1, name: 'Playing with Fire', series: 0, first: 0, category: -369, _category: 9 }], type: 1, id: 25962 },{ coords: [[79,44.2]], level: 0, name: 'Tobias Mistmantle', reacthorde: 0, reactalliance: 1, side: 1, quests: [{ level: 21, name: 'Clawing at the Truth', series: 1, first: 0, category: 10, _category: 0 },{ level: 21, name: 'Part of the Pack', series: 0, first: 0, category: 10, _category: 0 },{ level: 21, name: 'The Legend of Stalvan', series: 1, first: 1, category: 10, _category: 0 }], type: 1, id: 43453 },{ coords: [[18.4,57.6]], level: 0, name: 'Oliver Harris', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 21, name: 'Cry For The Moon', series: 1, first: 0, category: 10, _category: 0 },{ level: 22, name: 'A Curse We Cannot Lift', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 43730 },{ coords: [[19.8,57.8]], level: 0, name: 'Sister Elsington', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 22, name: 'Guided by the Light', series: 1, first: 0, category: 10, _category: 0 },{ level: 22, name: 'Soothing Spirits', series: 0, first: 0, category: 10, _category: 0 },{ level: 22, name: 'The Cries of the Dead', series: 0, first: 0, category: 10, _category: 0 },{ level: 24, name: 'Rebels Without a Clue', series: 1, first: 1, category: 33, _category: 0 }], type: 1, id: 43731 },{ coords: [[44.8,67.2]], level: 0, name: 'Apprentice Fess', reacthorde: -1, reactalliance: 1, side: 1, quests: [{ level: 22, name: 'A Deadly Vine', series: 0, first: 0, category: 10, _category: 0 },{ level: 22, name: 'Delivery to Master Harris', series: 1, first: 0, category: 10, _category: 0 },{ level: 22, name: 'The Yorgen Worgen', series: 1, first: 1, category: 10, _category: 0 }], type: 1, id: 43738 },{ coords: [[18.4,57.8]], level: 0, name: 'Sven Yorgen', reacthorde: 0, reactalliance: 1, side: 1, quests: [{ level: 22, name: 'The Fate of Morbent Fel', series: 1, first: 1, category: 10, _category: 0 },{ level: 22, name: 'The Lurking Lich', series: 1, first: 0, category: 10, _category: 0 }], type: 1, id: 43861 },{ coords: [[19.8,44.8]], level: 0, name: 'Eric Davidson', reacthorde: 1, reactalliance: 1, side: 1, quests: [{ level: 1, name: 'Eric Davidson', series: 0, first: 0, category: -394, _category: 7, daily: 1 },{ level: 1, name: 'Steven Lisbane', series: 1, first: 0, category: -394, _category: 7 }], type: 1, id: 65655 },{ coords: [[17.7,29.1]], level: 0, name: 'A Weathered Grave', side: 1, quests: [{ level: 23, name: 'The Weathered Grave', series: 1, first: 1, category: 10, _category: 0 }], type: 2, id: 61 },{ coords: [[23.5,35.5]], level: 0, name: 'Lightforged Rod', side: 1, quests: [{ level: 22, name: 'The Halls of the Dead', series: 1, first: 0, category: 10, _category: 0 }], type: 2, id: 204817 },{ coords: [[20.4,27.6]], level: 0, name: 'Lightforged Arch', side: 1, quests: [{ level: 22, name: 'Buried Below', series: 1, first: 0, category: 10, _category: 0 }], type: 2, id: 204824 },{ coords: [[18.1,25.3]], level: 0, name: 'Lightforged Crest', side: 1, quests: [{ level: 22, name: 'Morbent\'s Bane', series: 1, first: 0, category: 10, _category: 0 }], type: 2, id: 204825 }],
            1/2        hordequests: [{ coords: [[77.6,44.4]], level: 0, name: 'Chicken', reacthorde: 0, reactalliance: 0, side: 3, quests: [{ level: 1, name: 'CLUCK!', series: 0, first: 0, category: 40, _category: 0 }], type: 1, id: 620 },{ coords: [[19.8,44.8]], level: 0, name: 'Eric Davidson', reacthorde: 1, reactalliance: 1, side: 3, quests: [{ level: 1, name: 'Steven Lisbane', series: 1, first: 0, category: -394, _category: 7 }], type: 1, id: 65655 }],
            1          flightmaster: [{ coords: [[77.4,44.2]], level: 0, name: 'Felicia Maline', type: 1, id: 2409, reacthorde: -1, reactalliance: 1, description: 'Gryphon Master', paths: [[21,56.6]] },{ coords: [[21,56.6]], level: 0, name: 'John Shelby', type: 1, id: 43697, reacthorde: -1, reactalliance: 1, description: 'Gryphon Master' }],
                }
            */

            $this->map        = array(
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
            BUTTON_LINKS   => true
        );

    /*
        - sub zones..?
        - parent zone..?
        - associated with holiday?
        - spell_area ?
     */

        /**************/
        /* Extra Tabs */
        /**************/

        // tab: NPCs
        if ($cSpawns && !$creatureSpawns->error)
        {
            $lvData = array(
                'file'   => 'creature',
                'data'   => $creatureSpawns->getListviewData(),
                'params' => ['note' => sprintf(Util::$filterResultString, '?npcs&filter=cr=6;crs='.$this->typeId.';crv=0')]
            );

            if ($creatureSpawns->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $lvData['params']['_truncated'] = 1;

            $this->extendGlobalData($creatureSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = $lvData;
        }

        // tab: Objects
        if ($oSpawns && !$objectSpawns->error)
        {
            $lvData = array(
                'file'   => 'object',
                'data'   => $objectSpawns->getListviewData(),
                'params' => ['note' => sprintf(Util::$filterResultString, '?objects&filter=cr=1;crs='.$this->typeId.';crv=0')]
            );

            if ($objectSpawns->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                $lvData['params']['_truncated'] = 1;

            $this->extendGlobalData($objectSpawns->getJSGlobals(GLOBALINFO_SELF));

            $this->lvTabs[] = $lvData;
        }

        // tab: Quests

        // tab: items

        // tab: Quest Rewards

        // tab: achievements

        // tab: fished in zone
        $fish = new Loot();
        if ($fish->getByContainer(LOOT_FISHING, $this->typeId))
        {
            $this->extendGlobalData($fish->jsGlobals);
            $xCols = array_merge(['Listview.extraCols.percent'], $fish->extraCols);

            foreach ($fish->iterate() as $lv)
            {
                if (!$lv['quest'])
                    continue;

                $xCols = array_merge($xCols, ['Listview.extraCols.condition']);

                $reqQuest[$lv['id']] = 0;

                $lv['condition'][0][$this->typeId][] = [[CND_QUESTTAKEN, &$reqQuest[$lv['id']]]];
            }

            $this->lvTabs[] = array(
                'file'   => 'item',
                'data'   => $fish->getResult(),
                'params' => [
                    'name'        => '$LANG.tab_fishing',
                    'id'          => 'fishing',
                    'extraCols'   => $xCols ? "$[".implode(', ', array_unique($xCols))."]" : null,
                    'hiddenCols'  => "$['side']"
                ]
            );
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
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::$game['zone']));
    }

}

?>
