<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ZonesBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::ZONE;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'zones';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 6];

    protected  array  $scripts    = [[SC_JS_FILE, 'js/ShowOnMap.js']];
    protected  array  $validCats  = [true, true, [0, 1, 2], [0, 1, 2], false, false, true, false, true, true, true];

    public ?array $map = null;

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('zones'));


        /*************/
        /* Menu Path */
        /*************/

        foreach ($this->category as $c)
            $this->breadcrumb[] = $c;


        /**************/
        /* Page Title */
        /**************/

        if (isset($this->category[1]))
            array_unshift($this->title, Lang::game('expansions', $this->category[1]));

        if (isset($this->category[0]))
            array_unshift($this->title, Lang::zone('cat', $this->category[0]));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions  = [];                                  // do not limit
        $visibleCols = [];
        $hiddenCols  = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))             // sub-areas and unused zones
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
        {
            $conditions[] = ['z.category', $this->category[0]];
            $hiddenCols[] = 'category';

            if (isset($this->category[1]) && in_array($this->category[0], [2, 3]))
                $conditions[] = ['z.expansion', $this->category[1]];

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

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(array(
            'data'       => $zones->getListviewData(),
            'visibleCols'=> $visibleCols ?: null,
            'hiddenCols' => $hiddenCols  ?: null
        ), ZoneList::$brickFile));


        /**************/
        /* Flight Map */
        /**************/

        [$mapFile, $spawnMap] = match ($this->category[0] ?? null)
        {
             0      => [-3,   0],
             1      => [-6,   1],
             8      => [-2, 530],
            10      => [-5, 571],
            default => [ 0,  -1]
        };

        if ($mapFile)
        {
            $somData = ['flightmaster' => []];
            $nodes   = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, tn.* FROM ?_taxinodes tn WHERE `mapId` = ?d AND `type` <> 0 AND `typeId` <> 0', $spawnMap);
            $paths   = DB::Aowow()->select(
               'SELECT IF(tn1.`reactA` = tn1.`reactH` AND tn2.`reactA` = tn2.`reactH`, 1, 0) AS "neutral",
                       tp.`startNodeId` AS "startId", tn1.`posX` AS "startPosX", tn1.`posY` AS "startPosY",
                       tp.`endNodeId`   AS "endId",   tn2.`posX` AS "endPosX",   tn2.`posY` AS "endPosY"
                FROM   ?_taxipath tp, ?_taxinodes tn1, ?_taxinodes tn2
                WHERE  tn1.`Id` = tp.`endNodeId` AND tn2.`Id` = tp.`startNodeId` AND
                       tn1.`type` <> 0 AND tn2.`type` <> 0 AND
                       (tp.`startNodeId` IN (?a) OR tp.`EndNodeId` IN (?a))',
                array_keys($nodes), array_keys($nodes)
            );

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
                array(                                      // Mapper
                    'parent'   => 'mapper-generic',
                    'zone'     => $mapFile,
                    'zoom'     => 1,
                    'overlay'  => true,
                    'zoomable' => false
                ),
                null,                                       // mapperData
                $somData,                                   // ShowOnMap
                null                                        // foundIn
            );
        }

        parent::generate();
    }
}

?>
