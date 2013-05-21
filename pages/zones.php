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
    6 => true,
    8 => true,
    9 => true,
    10 => true
);

if (!Util::isValidPage($validCats, $cats))
    $smarty->error();

if (isset($cats[0]))
{
    if (isset($cats[1]))
        array_unshift($title, Lang::$game['expansions'][$cats[1]]);

    $path = array_merge($path, $cats);
    array_unshift($title, Lang::$zone['cat'][$cats[0]]);
}


if (!$smarty->loadCache($cacheKey, $pageData))
{
    $conditions  = [];
    $visibleCols = [];
    $hiddenCols  = [];

    if (isset($cats[0]))
        $conditions[] = ['z.category', $cats[0]];

    if (isset($cats[1]) && in_array($cats[0], [2, 3]))
        $conditions[] = ['z.expansion', $cats[1]];

    $zones = new ZoneList($conditions);

$somData = "{flightmaster: [{ coords: [[59.3,48.0]], level: 0, name: 'Thrallmar, Hellfire Peninsula', type: 1, id: 16587, reacthorde: 1, reactalliance: -1, paths: [[50.9,55.0],[49.1,72.1],[68.5,51.5],[60.9,61.3],[44.1,65.8]] },{ coords: [[58.9,55.8]], level: 0, name: 'Honor Hold, Hellfire Peninsula', type: 1, id: 16822, reacthorde: -1, reactalliance: 1, paths: [[50.1,48.3],[52.2,75.7],[68.6,52.8],[65.9,47.6],[44.1,65.8]] },{ coords: [[50.1,48.3]], level: 0, name: 'Temple of Telhamat, Hellfire Peninsula', type: 1, id: 18785, reacthorde: -1, reactalliance: 1, paths: [[39.7,48.2],[68.6,52.8]] },{ coords: [[50.9,55.0]], level: 0, name: 'Falcon Watch, Hellfire Peninsula', type: 1, id: 18942, reacthorde: 1, reactalliance: -1, paths: [[29.7,48.1],[33.6,60.8],[44.1,65.8],[68.5,51.5],[44.6,49.2]] },{ coords: [[39.7,48.2]], level: 0, name: 'Telredor, Zangarmarsh', type: 1, id: 18788, reacthorde: -1, reactalliance: 1, paths: [[32.6,73.4],[44.1,65.8],[35.5,31.2],[32.0,41.7],[42.7,34.0]] },{ coords: [[29.7,48.1]], level: 0, name: 'Zabra\'jin, Zangarmarsh', type: 1, id: 18791, reacthorde: 1, reactalliance: -1, paths: [[33.6,60.8],[44.1,65.8],[39.9,29.0],[44.6,49.2]] },{ coords: [[32.6,73.4]], level: 0, name: 'Telaar, Nagrand', type: 1, id: 18789, reacthorde: -1, reactalliance: 1, paths: [[52.2,75.7],[44.1,65.8]] },{ coords: [[33.6,60.8]], level: 0, name: 'Garadar, Nagrand', type: 1, id: 18808, reacthorde: 1, reactalliance: -1, paths: [[44.1,65.8]] },{ coords: [[52.2,75.7]], level: 0, name: 'Allerian Stronghold, Terokkar Forest', type: 1, id: 18809, reacthorde: -1, reactalliance: 1, paths: [[62.1,84.2],[44.1,65.8]] },{ coords: [[53.8,23.5]], level: 0, name: 'Area 52, Netherstorm', type: 1, id: 18938, reacthorde: 1, reactalliance: 1, paths: [[63.8,24.4],[57.5,14.3],[42.9,24.4]] },{ coords: [[59.8,75.9]], level: 0, name: 'Shadowmoon Village, Shadowmoon Valley', type: 1, id: 19317, reacthorde: 1, reactalliance: -1, paths: [[49.1,72.1],[70.1,76.3],[68.0,85.0]] },{ coords: [[62.1,84.2]], level: 0, name: 'Wildhammer Stronghold, Shadowmoon Valley', type: 1, id: 18939, reacthorde: -1, reactalliance: 1, paths: [[70.1,76.3],[68.0,85.0]] },{ coords: [[35.5,31.2]], level: 0, name: 'Sylvanaar, Blade\'s Edge Mountains', type: 1, id: 18937, reacthorde: -1, reactalliance: 1, paths: [[53.8,23.5],[57.5,14.3],[42.7,34.0],[42.9,24.4],[32.0,41.7]] },{ coords: [[39.9,29.0]], level: 0, name: 'Thunderlord Stronghold, Blade\'s Edge Mountains', type: 1, id: 18953, reacthorde: 1, reactalliance: -1, paths: [[53.8,23.5],[57.5,14.3],[44.6,49.2],[42.9,24.4],[47.5,32.6]] },{ coords: [[49.1,72.1]], level: 0, name: 'Stonebreaker Hold, Terokkar Forest', type: 1, id: 18807, reacthorde: 1, reactalliance: -1, paths: [[44.1,65.8]] },{ coords: [[44.1,65.8]], level: 0, name: 'Shattrath, Terokkar Forest', type: 1, id: 18940, reacthorde: 1, reactalliance: 1 },{ coords: [[68.6,52.8]], level: 0, name: 'Hellfire Peninsula, The Dark Portal', type: 1, id: 18931, reacthorde: -1, reactalliance: 1, paths: [[65.9,47.6]] },{ coords: [[68.5,51.5]], level: 0, name: 'Hellfire Peninsula, The Dark Portal', type: 1, id: 18930, reacthorde: 1, reactalliance: -1 },{ coords: [[57.5,14.3]], level: 0, name: 'The Stormspire, Netherstorm', type: 1, id: 19583, reacthorde: 1, reactalliance: 1, paths: [[63.8,24.4]] },{ coords: [[70.1,76.3]], level: 0, name: 'Altar of Sha\'tar, Shadowmoon Valley', type: 1, id: 19581, reacthorde: 1, reactalliance: 1 },{ coords: [[60.9,61.3]], level: 0, name: 'Spinebreaker Ridge, Hellfire Peninsula', type: 1, id: 19558, reacthorde: 1, reactalliance: -1 },{ coords: [[65.9,47.6]], level: 0, name: 'Shatter Point, Hellfire Peninsula', type: 1, id: 20234, reacthorde: -1, reactalliance: 1 },{ coords: [[63.8,24.4]], level: 0, name: 'Cosmowrench, Netherstorm', type: 1, id: 20515, reacthorde: 1, reactalliance: 1 },{ coords: [[44.6,49.2]], level: 0, name: 'Swamprat Post, Zangarmarsh', type: 1, id: 20762, reacthorde: 1, reactalliance: -1, paths: [[44.1,65.8],[47.5,32.6]] },{ coords: [[42.7,34.0]], level: 0, name: 'Toshley\'s Station, Blade\'s Edge Mountains', type: 1, id: 21107, reacthorde: -1, reactalliance: 1, paths: [[53.8,23.5],[42.9,24.4]] },{ coords: [[68.0,85.0]], level: 0, name: 'Sanctum of the Stars, Shadowmoon Valley', type: 1, id: 21766, reacthorde: 1, reactalliance: 1 },{ coords: [[42.9,24.4]], level: 0, name: 'Evergrove, Blade\'s Edge Mountains', type: 1, id: 22216, reacthorde: 1, reactalliance: 1 },{ coords: [[47.5,32.6]], level: 0, name: 'Mok\'Nathal Village, Blade\'s Edge Mountains', type: 1, id: 22455, reacthorde: 1, reactalliance: -1, paths: [[53.8,23.5]] },{ coords: [[32.0,41.7]], level: 0, name: 'Orebor Harborage, Zangarmarsh', type: 1, id: 22485, reacthorde: -1, reactalliance: 1 }]}";

    $pageData = array(
        'file'   => 'zone',
        'data'   => $zones->getListviewData(),
        'som'    => $somData,
        'map'    => array(
            'zone'     => -2,
            'zoom'     => 1,
            'overlay'  => 'true',
            'zoomable' => 'false',
        ),
        'params' => []
    );

    $smarty->saveCache($cacheKey, $pageData);
}

/*
	filters.js
	Mapper.js
	ShowOnMap.js

    new ShowOnMap();

*/

$page = array(
    'tab'   => 0,                                           // for g_initHeader($tab)
    'title' => implode(' - ', $title),
    'path'  => json_encode($path, JSON_NUMERIC_CHECK),
    'reqJS'     => array(
                       array('path' => 'template/js/Mapper.js', 'conditional' => false),
                       array('path' => 'template/js/ShowOnMap.js', 'conditional' => false),
                   ),
    'reqCSS'    => array(
                       array('path' => 'template/css/Mapper.css', 'condition' => false),
                       array('path' => 'template/css/Mapper_ie6.css', 'condition' => 'lte IE 6'),
                   ),
);

$smarty->updatePageVars($page);
$smarty->assign('lang', Lang::$main);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->assign('lvData', $pageData);
$smarty->display('generic-no-filter.tpl');

?>
