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


$smarty->updatePageVars(array(
    'title'  => Lang::$compare['compare'],
    'tab'    => 1,
    'reqCSS' => array(
        array('path' => 'template/css/Summary.css', 'condition' => false),
        array('path' => 'template/css/Summary_ie6.css', 'condition' => 'lte IE 6'),
    ),
    'reqJS'  => array(
        array('path' => 'template/js/Draggable.js'),
        array('path' => 'template/js/filters.js'),
        array('path' => 'template/js/Summary.js'),
        array('path' => 'template/js/swfobject.js'),
        array('path' => '?data=weight-presets.gems.enchants.itemsets'),
    ),
));
$smarty->assign('lvData', $pageData);
$smarty->assign('lang', array_merge(Lang::$main, Lang::$compare));
$smarty->display('compare.tpl');

?>
