<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


$petCalc = $pageCall == 'petcalc';

$page = array(
    'reqCSS' => array(
        array('path' => 'template/css/TalentCalc.css',      'condition' => false),
        array('path' => 'template/css/talent.css',          'condition' => false),
        array('path' => 'template/css/TalentCalc_ie6.css',  'condition' => 'lte IE 6'),
        array('path' => 'template/css/TalentCalc_ie67.css', 'condition' => 'lte IE 7')
    ),
    'reqJS'  => array(
        array('path' => '?data=glyphs'),
        array('path' => 'template/js/talent.js'),
        array('path' => 'template/js/TalentCalc.js')
    ),
    'title' => Lang::$talent['talentCalc'],
    'tab' => 1,
);

if ($petCalc)
{
    $page['reqCSS'][] = array('path' => 'template/css/petcalc.css', 'condition' => false);
    $page['reqJS'][0] = array('path' => '?data=pet-talents.pets');
    $page['reqJS'][1] = array('path' => 'template/js/petcalc.js');
    $page['reqJS'][]  = array('path' => 'template/js/swfobject.js');
    $page['title']    = Lang::$talent['petCalc'];
}


$smarty->updatePageVars($page);
$smarty->assign('tcType', $petCalc ? 'pc' : 'tc');
$smarty->assign('lang', array_merge(Lang::$main, Lang::$talent));
$smarty->display('talent.tpl');

?>
