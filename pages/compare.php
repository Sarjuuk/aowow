<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


$pageData      = ['items' => null, 'summary' => '[]'];
$compareString = '';

// prefer $_GET over $_COOKIE
if (!empty($_GET['compare']))
    $compareString = $_GET['compare'];
else if (!empty($_COOKIE['compare_groups']))
    $compareString = urldecode($_COOKIE['compare_groups']);

if ($compareString)
{
    $sets  = explode(";", $compareString);
    $items = [];
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

    $iList = new ItemList(array(['i.entry', $items]));
    $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);
    foreach ($data as $id => $item)
    {
        while ($iList->id != $id)
            $iList->iterate();

        $pageData['items'][] = [
            $id,
            Util::jsEscape($iList->getField('name', true)),
            $iList->getField('Quality'),
            $iList->getField('icon'),
            json_encode($item, JSON_NUMERIC_CHECK)
        ];
    }
}


// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'  => Lang::$compare['compare'],
    'tab'    => 1,
    'reqCSS' => array(
        ['path' => 'template/css/Summary.css'],
        ['path' => 'template/css/Summary_ie6.css', 'ieCond' => 'lte IE 6'],
    ),
    'reqJS'  => array(
        'template/js/Draggable.js',
        'template/js/filters.js',
        'template/js/Summary.js',
        'template/js/swfobject.js',
        '?data=weight-presets.gems.enchants.itemsets'
    ),
));
$smarty->assign('lvData', $pageData);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$compare));

// load the page
$smarty->display('compare.tpl');

?>
