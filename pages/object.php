<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage    = implode('_', [CACHETYPE_PAGE,    TYPE_OBJECT, $_id, -1, User::$localeId]);
$cacheKeyTooltip = implode('_', [CACHETYPE_TOOLTIP, TYPE_OBJECT, $_id, -1, User::$localeId]);

// AowowPower-request
if (isset($_GET['power']))
{
    header('Content-type: application/x-javascript; charsetUTF-8');

    Util::powerUseLocale(@$_GET['domain']);

    if (!$smarty->loadCache($cacheKeyTooltip, $x))
    {
        $object = new GameObjectList(array(['entry', $_id]));
        if ($object->error)
            die('$WowheadPower.registerObject('.$_id.', '.User::$localeId.', {});');

        $s = $object->getSpawns(true);

        $x  = '$WowheadPower.registerObject('.$_id.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($object->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".Util::jsEscape($object->renderTooltip())."'\n";
        // $x .= "\tmap: ".($s ? '{zone: '.$s[0].', coords: {0:'.json_encode($s[1], JSON_NUMERIC_CHECK).'}' : '{}')."\n";
        $x .= "});";

        $smarty->saveCache($cacheKeyTooltip, $x);
    }

    die($x);
}

// regular page
if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $object = new GameObjectList(array(['entry', $_id]));
    if ($object->error)
        $smarty->notFound(Lang::$game['gameObject']);

/*
    ListView for fishing holes

    id:'fished-in',
    hiddenCols:['instancetype', 'level', 'territory', 'category'],
    extraCols:[{if $percent}Listview.extraCols.percent{/if}],
    sort:['-percent', 'name'],

*/

    // NYI -> die()
    $smarty->error();



	// path(0, 5, $object['type']),

    // $object['starts'] = array();
    // $object['ends'] = array();
    // array(ACHIEVEMENT_CRITERIA_TYPE_USE_GAMEOBJECT, ACHIEVEMENT_CRITERIA_TYPE_FISH_IN_GAMEOBJECT),
    // $object['criteria_of'] = array();
    // object contains [..]

	$object['position'] = position($object['entry'], 'gameobject');
	// Исправить type, чтобы подсвечивались event-овые объекты
	if ($object['position'])
		foreach ($object['position'] as $z => $zone)
			foreach ($zone['points'] as $p => $pos)
				if ($pos['type'] == 0 && ($events = event_find(array('object_guid' => $pos['guid']))))
				{
					$names = arraySelectKey(event_name($events), 'name');
					$object['position'][$z]['points'][$p]['type'] = 4;
					$object['position'][$z]['points'][$p]['events'] = implode(", ", $names);
				}


    $smarty->saveCache($cacheKeyPage, $pageData);
}


// menuId 5: Object   g_initPath()
//  tabId 0: Database g_initHeader()
$smarty->updatePageVars(array(
	'title'  => $pageData['title'].' - '.Util::ucFirst(Lang::$game['gameObject']),
	'path'   => $pageData['path'],
    'tab'    => 0,
	'type'   => TYPE_OBJECT,
	'typeId' => $_id,
	'reqCSS' => array(
        $object['pageText'] ? ['path' => 'template/css/Book.css'] : null,
        ['path' => 'template/css/Mapper.css'],
        ['path' => 'template/css/Mapper_ie6.css', 'ieCond' => 'lte IE 6']
    ),
	'reqJS'  => array(
        $object['pageText'] ? 'template/js/Book.js' : null,
        'template/js/Mapper.js'
    )
));
$smarty->assign('community', CommunityContent::getAll(TYPE_OBJECT, $_id));         // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, Lang::$object, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('object.tpl');

?>
