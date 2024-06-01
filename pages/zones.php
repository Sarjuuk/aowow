<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 6: Zone     g_initPath()
//  tabId 0: Database g_initHeader()
class ZonesPage extends GenericPage
{
    use TrListPage;

    protected $map       = null;

    protected $type      = Type::ZONE;
    protected $tpl       = 'list-page-generic';
    protected $path      = [0, 6];
    protected $tabId     = 0;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [true, true, [0, 1, 2], [0, 1, 2], false, false, true, false, true, true, true];
    protected $scripts   = [[SC_JS_FILE, 'js/ShowOnMap.js']];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('zones'));
    }

    protected function generateContent()
    {
        $conditions  = [Cfg::get('SQL_LIMIT_NONE')];
        $visibleCols = [];
        $hiddenCols  = [];
        $mapFile     = 0;
        $spawnMap    = -1;

        if (!User::isInGroup(U_GROUP_EMPLOYEE))             // sub-areas and unused zones
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
        {
            $conditions[] = ['z.category', $this->category[0]];
            $hiddenCols[] = 'category';

            if (isset($this->category[1]) && in_array($this->category[0], [2, 3]))
                $conditions[] = ['z.expansion', $this->category[1]];

            if (empty($this->category[1]))
            {
                switch ($this->category[0])
                {
                    case  0:    $mapFile = -3;  $spawnMap = 0;      break;
                    case  1:    $mapFile = -6;  $spawnMap = 1;      break;
                    case  8:    $mapFile = -2;  $spawnMap = 530;    break;
                    case 10:    $mapFile = -5;  $spawnMap = 571;    break;
               }
            }

            switch ($this->category[0])
            {
                case 6:
                case 2:
                case 3:
                    array_push($visibleCols, 'level', 'players');
                case 9:
                    $hiddenCols[] = 'territory';
                    break;
            }
        }

        $zones = new ZoneList($conditions);

        if (!$zones->hasSetFields('type'))
            $hiddenCols[] = 'instancetype';

        $tabData = ['data' => array_values($zones->getListviewData())];

        if ($visibleCols)
            $tabData['visibleCols'] = $visibleCols;

        if ($hiddenCols)
            $tabData['hiddenCols'] = $hiddenCols;

        $this->lvTabs[] = [ZoneList::$brickFile, $tabData];

        // create flight map
        if ($mapFile)
        {
            $somData = ['flightmaster' => []];
            $nodes   = DB::Aowow()->select('SELECT id AS ARRAY_KEY, tn.* FROM ?_taxinodes tn WHERE mapId = ?d AND type <> 0 AND typeId <> 0', $spawnMap);
            $paths   = DB::Aowow()->select('
                        SELECT  IF(tn1.reactA = tn1.reactH AND tn2.reactA = tn2.reactH, 1, 0) AS neutral,
                                tp.startNodeId AS startId,
                                tn1.posX AS startPosX,
                                tn1.posY AS startPosY,
                                tp.endNodeId AS endId,
                                tn2.posX AS endPosX,
                                tn2.posY AS endPosY
                        FROM    ?_taxipath tp,
                                ?_taxinodes tn1,
                                ?_taxinodes tn2
                        WHERE   tn1.Id = tp.endNodeId AND
                                tn2.Id = tp.startNodeId AND
                                tn1.type <> 0 AND
                                tn2.type <> 0 AND
                                (tp.startNodeId IN (?a) OR tp.EndNodeId IN (?a))
                        ', array_keys($nodes), array_keys($nodes));

            foreach ($nodes as $i => $n)
            {
                $neutral = $n['reactH'] == $n['reactA'];

                $data = array(
                    'coords'        => [[$n['posX'], $n['posY']]],
                    'level'         => 0,                   // floor
                    'name'          => Util::localizedString($n, 'name'),
                    'type'          => $n['type'],
                    'id'            => $n['typeId'],
                    'reacthorde'    => $n['reactH'],
                    'reactalliance' => $n['reactA'],
                    'paths'         => []
                );

                foreach ($paths as $j => $p)
                {
                    if ($i != $p['startId'] && $i != $p['endId'])
                        continue;

                    if ($i == $p['startId'] && (!$neutral || $p['neutral']))
                    {
                        $data['paths'][] = [$p['startPosX'], $p['startPosY']];
                        unset($paths[$j]);
                    }
                    else if ($i == $p['endId'] && (!$neutral || $p['neutral']))
                    {
                        $data['paths'][] = [$p['endPosX'], $p['endPosY']];
                        unset($paths[$j]);
                    }
                }

                if (empty($data['paths']))
                    unset($data['paths']);

                $somData['flightmaster'][] = $data;
            }

            $this->map = array(
                'data' => array(
                    'zone'     => $mapFile,
                    'zoom'     => 1,
                    'overlay'  => true,
                    'zoomable' => false,
                    'parent'   => 'mapper-generic'
                ),
                'som' => $somData,
                'mapperData' => [$mapFile => new stdClass()]
            );
        }
    }

    protected function generateTitle()
    {
        if ($this->category)
        {
            if (isset($this->category[1]))
                array_unshift($this->title, Lang::game('expansions', $this->category[1]));

            array_unshift($this->title, Lang::zone('cat', $this->category[0]));
        }
    }

    protected function generatePath()
    {
        foreach ($this->category as $c)
            $this->path[] = $c;
    }
}


?>
