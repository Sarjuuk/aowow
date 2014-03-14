<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');



// tabId 1: Tools g_initHeader()
$pageData      = array(
    'items'   => null,
    'summary' => '[]',
    'title'   => Lang::$main['compareTool'],
    'tab'     => 1,
    'reqCSS'  => array(
        ['path' => 'template/css/Summary.css'],
        ['path' => 'template/css/Summary_ie6.css', 'ieCond' => 'lte IE 6'],
    ),
    'reqJS'   => array(
        'template/js/profile.js',
        'template/js/Draggable.js',
        'template/js/filters.js',
        'template/js/Summary.js',
        'template/js/swfobject.js',
        '?data=weight-presets.gems.enchants.itemsets'
    )
);
$compareString = '';

// prefer $_GET over $_COOKIE
if (!empty($_GET['compare']))
    $compareString = $_GET['compare'];
else if (!empty($_COOKIE['compare_groups']))
    $compareString = urldecode($_COOKIE['compare_groups']);

if ($compareString)
{
    $sets  = explode(";", $compareString);
    $items = $outSet = [];
    foreach ($sets as $set)
    {
        $itemsting = explode(":", $set);
        $outString = [];
        foreach ($itemsting as $substring)
        {
            $params  = explode(".", $substring);
            $items[] = (int)$params[0];
            while (sizeof($params) < 7)
                $params[] = 0;

            $outString[] = $params;
        }

        $outSet[] = $outString;
    }
    $pageData['summary'] = json_encode($outSet, JSON_NUMERIC_CHECK);

    $iList = new ItemList(array(['i.id', $items]));
    $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);

    foreach ($iList->iterate() as $itemId => $__)
    {
        if (empty($data[$itemId]))
            continue;

        $pageData['items'][] = [
            $itemId,
            Util::jsEscape($iList->getField('name', true)),
            $iList->getField('quality'),
            $iList->getField('iconString'),
            json_encode($data[$itemId], JSON_NUMERIC_CHECK)
        ];
    }
}


$smarty->updatePageVars($pageData);
$smarty->assign('lang', Lang::$main);

// load the page
$smarty->display('compare.tpl');

?>
