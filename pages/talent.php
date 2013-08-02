<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


if (!isset($petCalc))
    $petCalc = false;

// tabId 1: Tools g_initHeader()
$smarty->updatePageVars(array(
    'title'  => $petCalc ? Lang::$talent['petCalc'] : Lang::$talent['talentCalc'],
    'tab'    => 1,
    'reqCSS' => array(
        ['path' => 'template/css/TalentCalc.css'],
        ['path' => 'template/css/talent.css'],
        ['path' => 'template/css/TalentCalc_ie6.css',  'ieCond' => 'lte IE 6'],
        ['path' => 'template/css/TalentCalc_ie67.css', 'ieCond' => 'lte IE 7'],
        $petCalc ? ['path' => 'template/css/petcalc.css'] : null
    ),
    'reqJS'  => array(
        'template/js/TalentCalc.js',
        $petCalc ? '?data=pet-talents.pets'   : '?data=glyphs',
        $petCalc ? 'template/js/petcalc.js'   : 'template/js/talent.js',
        $petCalc ? 'template/js/swfobject.js' : null
    )
));
$smarty->assign('tcType', $petCalc ? 'pc' : 'tc');
$smarty->assign('lang', array_merge(Lang::$main, Lang::$talent));

// load the page
$smarty->display('talent.tpl');

?>
