<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (!isset($petCalc))
    $petCalc = false;

// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'  => $petCalc ? Lang::$main['petCalc'] : Lang::$main['talentCalc'],
    'tab'    => 1,
    'reqCSS' => array(
        ['path' => 'static/css/TalentCalc.css'],
        ['path' => 'static/css/talent.css'],
        ['path' => 'static/css/TalentCalc_ie6.css',  'ieCond' => 'lte IE 6'],
        ['path' => 'static/css/TalentCalc_ie67.css', 'ieCond' => 'lte IE 7'],
        $petCalc ? ['path' => 'static/css/petcalc.css'] : null
    ),
    'reqJS'  => array(
        'static/js/TalentCalc.js',
        $petCalc ? '?data=pet-talents.pets'   : '?data=glyphs',
        $petCalc ? 'static/js/petcalc.js'   : 'static/js/talent.js',
        $petCalc ? 'static/js/swfobject.js' : null
    )
));
$smarty->assign('tcType', $petCalc ? 'pc' : 'tc');
$smarty->assign('lang', array_merge(Lang::$main, ['colon' => Lang::$colon]));

// load the page
$smarty->display('talent.tpl');

?>
