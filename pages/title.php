<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require 'includes/community.class.php';

$_id = intVal($pageParam);

$cacheKeyPage = implode('_', [CACHETYPE_PAGE, TYPE_TITLE, $_id, -1, User::$localeId]);

if (!$smarty->loadCache($cacheKeyPage, $pageData))
{
    $title = new TitleList(array(['id', $_id]));
    if ($title->error)
        $smarty->notFound(Lang::$game['title'], $_id);

    /***********/
    /* Infobox */
    /***********/

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

    /****************/
    /* Main Content */
    /****************/

    // menuId 10: Title    g_initPath()
    //  tabId  0: Database g_initHeader()
    $pageData = array(
        'page'    => array(
            'title'      => Util::ucFirst(trim(str_replace('%s', '', str_replace(',', '', $title->getField('male', true)))))." - ".Util::ucfirst(Lang::$game['title']),
            'path'       => '[0, 10, '.$title->getField('category').']',
            'tab'        => 0,
            'type'       => TYPE_TITLE,
            'typeId'     => $_id,
            'name'       => $title->getHtmlizedName(),
            'infobox'    => $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null,
            'expansion'  => Util::$expansionString[$title->getField('expansion')],
            'redButtons' => array(
                BUTTON_WOWHEAD => true,
                BUTTON_LINKS   => true
            ),
        ),
        'relTabs' => []
    );

    /**************/
    /* Extra Tabs */
    /**************/

    // tab: sources
    if (!empty($title->sources[$_id]))
    {
        foreach ($title->sources[$_id] as $type => $entries)
        {
            switch ($type)
            {
                case  4:
                    $quests = new QuestList(array(['id', $entries]));
                    $quests->addGlobalsToJscript($smarty, GLOBALINFO_REWARDS);

                    $pageData['relTabs'][] = array(
                        'file'   => 'quest',
                        'data'   => $quests->getListviewData(),
                        'params' => array(
                            'id'          => 'reward-from-quest',
                            'name'        => '$LANG.tab_rewardfrom',
                            'hiddenCols'  => "$['experience', 'money']",
                            'visibleCols' => "$['category']",
                            'tabs'        => '$tabsRelated'
                        )
                    );
                    break;
                case 12:
                    $acvs = new AchievementList(array(['id', $entries]));
                    $acvs->addGlobalsToJscript($smarty);

                    $pageData['relTabs'][] = array(
                        'file'   => 'achievement',
                        'data'   => $acvs->getListviewData(),
                        'params' => array(
                            'id'          => 'reward-from-achievement',
                            'name'        => '$LANG.tab_rewardfrom',
                            'visibleCols' => "$['category']",
                            'sort'        => "$['reqlevel', 'name']",
                            'tabs'        => '$tabsRelated'
                        )
                    );
                    break;
                // case 13:
                    // not displayed
            }
        }
    }

    // tab: criteria of (to be added by TC)

    $smarty->saveCache($cacheKeyPage, $pageData);
}


$smarty->updatePageVars($pageData['page']);
$smarty->assign('community', CommunityContent::getAll(TYPE_TITLE, $_id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('lvData', $pageData['relTabs']);

// load the page
$smarty->display('detail-page-generic.tpl');

?>
