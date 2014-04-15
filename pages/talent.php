<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (!isset($petCalc))
    $petCalc = false;

// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'   => $petCalc ? Lang::$main['petCalc'] : Lang::$main['talentCalc'],
    'tab'     => 1,
    'dataKey' => $_SESSION['dataKey'],
    'reqCSS'  => array(
        ['path' => STATIC_URL.'/css/TalentCalc.css'],
        ['path' => STATIC_URL.'/css/talent.css'],
        ['path' => STATIC_URL.'/css/TalentCalc_ie6.css',  'ieCond' => 'lte IE 6'],
        ['path' => STATIC_URL.'/css/TalentCalc_ie67.css', 'ieCond' => 'lte IE 7'],
        $petCalc ? ['path' => STATIC_URL.'/css/petcalc.css'] : null
    ),
    'reqJS'  => array(
        STATIC_URL.'/js/TalentCalc.js',
       ($petCalc ? '?data=pet-talents.pets' : '?data=glyphs').'&locale='.User::$localeId.'&t='.$_SESSION['dataKey'],
        $petCalc ? STATIC_URL.'/js/petcalc.js'   : STATIC_URL.'/js/talent.js',
        $petCalc ? STATIC_URL.'/js/swfobject.js' : null
    )
));
$smarty->assign('tcType', $petCalc ? 'pc' : 'tc');
$smarty->assign('lang', array_merge(Lang::$main, ['colon' => Lang::$colon]));

// load the page
$smarty->display('talent.tpl');

?>
