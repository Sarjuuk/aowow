<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$cats      = Util::extractURLParams($pageParam);
$path      = [0, 6];
$title     = [Util::ucFirst(Lang::$game['zones'])];
$cacheKey  = implode('_', [CACHETYPE_PAGE, TYPE_ZONE, -1, $cats[0] ? implode('.', $cats) : -1, User::$localeId]);
$validCats = array(
    0 => true,
    1 => true,
    2 => [0, 1, 2],
    3 => [0, 1, 2],
    4 => true,
    6 => true,
    8 => true,
    9 => true,
    10 => true
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (!$smarty->loadCache($cacheKey, $pageData))
{
    $conditions  = [];
    $visibleCols = [];
    $hiddenCols  = [];
    $mapFile     = 0;
    $spawnMap    = -1;

    if (isset($cats[0]))
    {
        $conditions[] = ['z.category', $cats[0]];

        if (isset($cats[1]) && in_array($cats[0], [2, 3]))
            $conditions[] = ['z.expansion', $cats[1]];

        if (isset($cats[1]))
            array_unshift($title, Lang::$game['expansions'][$cats[1]]);
        else
        {
            switch ($cats[0])
            {
                case  0:    $mapFile = -3;  $spawnMap = 0;      break;
                case  1:    $mapFile = -6;  $spawnMap = 1;      break;
                case  8:    $mapFile = -2;  $spawnMap = 530;    break;
                case 10:    $mapFile = -5;  $spawnMap = 571;    break;
            }
        }

        $path = array_merge($path, $cats);
        array_unshift($title, Lang::$zone['cat'][$cats[0]]);
    }

    $zones = new ZoneList($conditions);

    $pageData = array(
        'file'   => 'zone',
        'data'   => $zones->getListviewData(),
        'som'    => '',
        'map'    => [],
        'params' => []
    );

    if ($mapFile)
    {
        $somData = ['flightmaster' => []];
        $nodes   = DB::Aowow()->select('SELECT id AS ARRAY_KEY, tn.* FROM ?_taxiNodes tn WHERE mapId = ?d ', $spawnMap);
        $paths   = DB::Aowow()->select('SELECT IF(tn1.reactA = tn1.reactH AND tn2.reactA = tn2.reactH, 1, 0) AS neutral, tp.startNodeId AS startId, tn1.posX AS startPosX, tn1.posY AS startPosY, tp.endNodeId AS endId, tn2.posX AS endPosX, tn2.posY AS endPosY FROM ?_taxiPath tp, ?_taxiNodes tn1 , ?_taxiNodes tn2 WHERE tn1.Id = tp.endNodeId AND tn2.Id = tp.startNodeId AND (tp.startNodeId IN (?a) OR tp.EndNodeId IN (?a))', array_keys($nodes), array_keys($nodes));

        foreach ($nodes as $i => $n)
        {
            $neutral = $n['reactH'] == $n['reactA'];

            $data = array(
                'coords'        => [[$n['posX'], $n['posY']]],
                'level'         => 0,                       // floor
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

        $pageData['map'] = array(
            'zone'     => $mapFile,
            'zoom'     => 1,
            'overlay'  => 'true',
            'zoomable' => 'false'
        );

        $pageData['som'] = json_encode($somData, JSON_NUMERIC_CHECK);
    }

    $smarty->saveCache($cacheKey, $pageData);
}

$page = array(
    'tab'    => 0,                                           // for g_initHeader($tab)
    'title'  => implode(' - ', $title),
    'path'   => json_encode($path, JSON_NUMERIC_CHECK),
    'reqJS'  => array(
        array('path' => 'template/js/Mapper.js',    'conditional' => false),
        array('path' => 'template/js/ShowOnMap.js', 'conditional' => false),
    ),
    'reqCSS' => array(
        array('path' => 'template/css/Mapper.css',     'condition' => false),
        array('path' => 'template/css/Mapper_ie6.css', 'condition' => 'lte IE 6'),
    ),
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('lvData', $pageData);
$smarty->display('generic-no-filter.tpl');

?>
