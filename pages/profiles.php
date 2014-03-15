<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

// !do not cache!
/* older version
new Listview({
    template: 'profile',
    id: 'characters',
    name: LANG.tab_characters,
    parent: 'lkljbjkb574',
    visibleCols: ['race','classs','level','talents','gearscore','achievementpoints','rating'],
    sort: [-15],
    hiddenCols: ['arenateam','guild','location'],
    onBeforeCreate: pr_initRosterListview,
    data: [
        {id:30577430,name:'Çircus',achievementpoints:0,guild:'swaggin',guildrank:5,arenateam:{2:{name:'the bird knows the word',rating:1845}},realm:'maiev',realmname:'Maiev',battlegroup:'whirlwind',battlegroupname:'Whirlwind',region:'us',roster:2,row:1},
        {id:10602015,name:'Gremiss',achievementpoints:3130,guild:'Team Discovery Channel',guildrank:3,arenateam:{2:{name:'the bird knows the word',rating:1376}},realm:'maiev',realmname:'Maiev',battlegroup:'whirlwind',battlegroupname:'Whirlwind',region:'us',level:80,race:5,gender:1,classs:9,faction:1,gearscore:2838,talenttree1:54,talenttree2:17,talenttree3:0,talentspec:1,roster:2,row:2}
    ]
});
*/
    $lv = array(
        array(
            'id' => '0',
            'name' => 'Helluvah',
            'acvPts' => '6095',
            'guildName' => 'Been There Done That',
            'guildRank' => '-1',
            'realmInternal' => 'aszune',
            'realmName' => 'Aszune',
            'bgInternal' => 'blackout',
            'bgName' => 'Blackout',
            'region' => 'eu',
            'level' => '85',
            'race' => '1',
            'gender' => '1',
            'class' => '2',
            'faction' => '0',
            'tree' => [31, 5, 5],
            'spec' => '1'
        ),
        array(
            'id' => '0',
            'name' => 'Vetzew',
            'acvPts' => '9830',
            'guildName' => 'SPQ',
            'guildRank' => '-1',
            'realmInternal' => 'aszune',
            'realmName' => 'Aszune',
            'bgInternal' => 'blackout',
            'bgName' => 'Blackout',
            'region' => 'eu',
            'level' => '85',
            'race' => '1',
            'gender' => '0',
            'class' => '5',
            'faction' => '0',
            'tree' => [9, 32, 0],
            'spec' => '2'
        ),
        array(
            'id' => '0',
            'name' => 'Winry',
            'acvPts' => '9065',
            'guildName' => 'Momentum',
            'guildRank' => '-1',
            'realmInternal' => 'aszune',
            'realmName' => 'Aszune',
            'bgInternal' => 'blackout',
            'bgName' => 'Blackout',
            'region' => 'eu',
            'level' => '85',
            'race' => '10',
            'gender' => '1',
            'class' => '3',
            'faction' => '1',
            'tree' => [1, 31, 9],
            'spec' => '2'
        ),
        array(
            'id' => '0',
            'name' => 'Enfisk',
            'acvPts' => '12370',
            'guildName' => 'Postal',
            'guildRank' => '-1',
            'realmInternal' => 'aszune',
            'realmName' => 'Aszune',
            'bgInternal' => 'blackout',
            'bgName' => 'Blackout',
            'region' => 'eu',
            'level' => '85',
            'race' => '3',
            'gender' => '1',
            'class' => '5',
            'faction' => '0',
            'tree' => [9, 0, 32],
            'spec' => '3'
        ),
        array(
            'id' => '71',
            'name' => 'Erchenmar',
            'acvPts' => '10570',
            'guildName' => 'Psychosomatic Assassins',
            'guildRank' => '-1',
            'realmInternal' => 'aszune',
            'realmName' => 'Aszune',
            'bgInternal' => 'blackout',
            'bgName' => 'Blackout',
            'region' => 'eu',
            'level' => '85',
            'race' => '5',
            'gender' => '0',
            'class' => '9',
            'faction' => '0',
            'tree' => [3, 7, 31],
            'spec' => '3'
        )
    );


    // dont send ID for real chars unless they have some kind of custom avatar
    // on second thought .. ids are required, but the function that generates the icon is faulty

$pageData = array(
    'file'   => 'class',
    'data'   => $lv,
    'params' => [
        'id'          => 'characters',
        'name'        => '$LANG.tab_characters',
        'hideCount'   => 1,
        '_truncated'  => 1,
        'visibleCols' => "$['race','classs','level','talents','achievementpoints']",
        'note'        => '$$WH.sprintf(LANG.lvnote_charactersfound, \'20,592,390\', 200) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')',
        'onBeforeCreate' => '$pr_initRosterListview'        // $_GET['roster'] = 1|2|3|4 .. 2,3,4 arenateam-size (4 => 5-man), 1 guild .. it puts a resync button on the lv...
    ]
);


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
$smarty->updatePageVars(array(
    'tab'   => 1,
    'title' => Util::ucFirst(Lang::$game['profiles']),
    'path'  => "[1, 5, 0, 'us', 'pure-pwnage', 'trinity']",                                    // [1,5,'eu','cyclone-wirbelsturm','silvermoon']
    'tab'   => 1,
    'reqJS' => array(
        'static/js/filters.js?978',
        'static/js/profile_all.js?978',
        'static/js/profile.js?978',
        '?data=weight-presets.realms&978',
        // '?data=user&1280640186'
    ),
    'reqCSS' => array(
        ['path' => 'static/css/profiler.css']
    )
));
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);

// load the page
$smarty->display('profiles.tpl');

?>
