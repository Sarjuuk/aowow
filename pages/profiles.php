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

@include('datasets/ProfilerExampleChar');                   // tmp char data

$finalDis = [];
$tString  = $character['talents']['builds'][$character['talents']['active']]['talents'];
$start    = 0;
$distrib  = DB::Aowow()->selectCol('SELECT COUNT(t.id) FROM dbc.talent t JOIN dbc.talenttab tt ON t.tabId = tt.id WHERE tt.classMask & ?d GROUP BY tt.id ORDER BY tt.tabNumber ASC', 1 << ($character['classs'] - 1));
foreach ($distrib as $d)
{
    $tSub = substr($tString, $start, $d);
    $qty  = 0;
    for ($i = 0; $i < strlen($tSub); $i++)
        $qty += (int)$tSub[$i];

    $finalDis[] = $qty;
    $start += $d;
}

$lv = array(
    array(
        'id'            => 0,
        'name'          => $character['name'],
        'acvPts'        => $character['achievementpoints'],
        'guildName'     => $character['guild'],
        'guildRank'     => -1,
        'realmInternal' => $character['realm'][0],
        'realmName'     => $character['realm'][1],
        'bgInternal'    => $character['battlegroup'][0],
        'bgName'        => $character['battlegroup'][0],
        'region'        => $character['region'][0],
        'level'         => $character['level'],
        'race'          => $character['race'],
        'gender'        => $character['gender'],
        'class'         => $character['classs'],
        'faction'       => $character['faction'],
        'tree'          => $finalDis,
        'spec'          => $character['talents']['active']
    )
);

// dont send ID for real chars unless they have some kind of custom avatar
// on second thought .. ids are required for resync, but the function that generates the icon is faulty

$pageData = array(
    'file'   => 'class',
    'data'   => $lv,
    'params' => [
        'id'          => 'characters',
        'name'        => '$LANG.tab_characters',
        'hideCount'   => 1,
        '_truncated'  => 1,
        'roster'      => 3,
        'visibleCols' => "$['race','classs','level','talents','achievementpoints']",
        'note'        => '$$WH.sprintf(LANG.lvnote_charactersfound, \'20,592,390\', 200) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')',
        'onBeforeCreate' => '$pr_initRosterListview'        // $_GET['roster'] = 1|2|3|4 .. 2,3,4 arenateam-size (4 => 5-man), 1 guild .. it puts a resync button on the lv...
    ]
);


// menuId 5: Profiler g_initPath()
//  tabId 1: Tools    g_initHeader()
$smarty->updatePageVars(array(
    'tab'    => 1,
    'title'  => Util::ucFirst(Lang::$game['profiles']),
    'path'   => "[1, 5, 0, '".$character['region'][0]."', '".$character['battlegroup'][0]."', '".$character['realm'][0]."']",
    'realm'  => '',                                         // not sure about the use
    'region' => '',                                         // seconded..
    'reqJS'  => array(
        STATIC_URL.'/js/filters.js',
        STATIC_URL.'/js/profile_all.js',
        STATIC_URL.'/js/profile.js',
        '?data=weight-presets.realms&locale='.User::$localeId.'&t='.$_SESSION['dataKey']
    ),
    'reqCSS' => array(
        ['path' => STATIC_URL.'/css/profiler.css']
    )
));
$smarty->assign('lang', array_merge(Lang::$main, Lang::$game, ['colon' => Lang::$colon]));
$smarty->assign('lvData', $pageData);
$smarty->assign('filter', ['query' => 1]);

// load the page
$smarty->display('profiles.tpl');

?>
