<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/class.community.php';

$id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_TITLE, $id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $title = new TitleList(array(['id', $id]));
    if ($title->error)
        $smarty->notFound(Lang::$game['title']);

    $infobox = [];
    if ($title->getField('side') == SIDE_ALLIANCE)
        $infobox[] = Lang::$main['side'].Lang::$colon.'[span class=alliance-icon]'.Lang::$game['si'][SIDE_ALLIANCE].'[/span]';
    else if ($title->getField('side') == SIDE_HORDE)
        $infobox[] = Lang::$main['side'].Lang::$colon.'[span class=horde-icon]'.Lang::$game['si'][SIDE_HORDE].'[/span]';
    else
        $infobox[] = Lang::$main['side'].Lang::$colon.Lang::$game['si'][SIDE_BOTH];

    if ($g = $title->getField('gender'))
        $infobox[] = Lang::$main['gender'].Lang::$colon.'[span class='.($g == 2 ? 'female' : 'male').'-icon]'.Lang::$main['sex'][$g].'[/span]';

    if ($e = $title->getField('eventId'))
        $infobox[] = Lang::$game['eventShort'].Lang::$colon.'[url=?event='.$e.']'.WorldEventList::getName($e).'[/url]';

    $pageData = array(
        'page' => array(
            'name'      => $title->getHtmlizedName(),
            'id'        => $id,
            'expansion' => Util::$expansionString[$title->getField('expansion')]
        ),
        'infobox' => '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]',
    );

    if (!empty($title->sources[$id]))
    {
        foreach ($title->sources[$id] as $type => $entries)
        {
            switch ($type)
            {
                case  4:
                    $quests = new QuestList(array(['id', $entries]));
                    $quests->addGlobalsToJscript($smarty, GLOBALINFO_REWARDS);

                    $pageData['page']['questReward'] = $quests->getListviewData();
                    $pageData['page']['questParams'] = array(
                        'id'            => 'reward-from-quest',
                        'name'          => '$LANG.tab_rewardfrom',
                        'hiddenCols'    => "$['experience', 'money']",
                        'visibleCols'   => "$['category']",
                        'tabs'          => '$tabsRelated'
                    );
                    break;
                case 12:
                    $acvs = new AchievementList(array(['id', $entries]));
                    $acvs->addGlobalsToJscript($smarty);

                    $pageData['page']['acvReward'] = $acvs->getListviewData();
                    $pageData['page']['acvParams'] = array(
                        'id'            => 'reward-from-achievement',
                        'name'          => '$LANG.tab_rewardfrom',
                        'visibleCols'   => "$['category']",
                        'sort'          => "$['reqlevel', 'name']",
                        'tabs'          => '$tabsRelated'
                    );
                    break;
                // case 13:
                    // not displayed
            }
        }
    }

    $pageData['title'] = Util::ucFirst(trim(str_replace('%s', '', str_replace(',', '', $title->getField('male', true)))));
    $pageData['path']  = '[0, 10, '.$title->getField('category').']';

    $smarty->saveCache($cacheKeyPage, $pageData);
}

$smarty->updatePageVars(array(
    'title'  => $pageData['title']." - ".Util::ucfirst(Lang::$game['title']),
    'path'   => $pageData['path'],
    'tab'    => 0,                                          // for g_initHeader($tab)
    'type'   => TYPE_TITLE,                                 // 11:Titles
    'typeId' => $id
));


$smarty->assign('community', CommunityContent::getAll(TYPE_TITLE, $id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData);
$smarty->display('title.tpl');

?>
