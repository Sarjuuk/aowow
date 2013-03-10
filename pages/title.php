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
    {
        $smarty->updatePageVars(array(
            'subject'  => ucfirst(Lang::$game['title']),
            'id'       => $id,
            'notFound' => sprintf(Lang::$main['pageNotFound'], Lang::$game['title']),
        ));
        $smarty->assign('lang', Lang::$main);
        $smarty->display('404.tpl');
        exit();
    }
    else
    {
        $title->addGlobalsToJscript($pageData);

        $infobox = [];
        $colon   = User::$localeId == LOCALE_FR ? ' : ' : ': '; // Je suis un prick! <_<
        if ($title->getField('side') == 1)
            $infobox[] = Lang::$main['side'].$colon.'[span class=alliance-icon]'.Lang::$game['alliance'].'[/span]';
        else if ($title->getField('side') == 2)
            $infobox[] = Lang::$main['side'].$colon.'[span class=horde-icon]'.Lang::$game['horde'].'[/span]';
        else
            $infobox[] = Lang::$main['side'].$colon.Lang::$main['both'];

        if ($g = $title->getField('gender'))
            $infobox[] = Lang::$main['gender'].$colon.'[span class='.($g == 2 ? 'female' : 'male').'-icon]'.Lang::$main['sex'][$g].'[/span]';

        if ($e = $title->getField('eventId'))
            $infobox[] = Lang::$game['eventShort'].$colon.'[url=?event='.$e.']'.WorldEvent::getName($e).'[/url]';

        $pageData = array(
            'page' => array(
                'name'      => $title->getHtmlizedName(),
                'id'        => $id,
                'expansion' => Util::$expansionString[$title->getField('expansion')]
            ),
            'infobox' => '[li][ul]'.implode('[/ul][ul]', $infobox).'[/ul][/li]',
        );

        foreach ($title->sources[$id] as $type => $entries)
        {
            // todo: hidden-/visibleCols by actual use
            switch ($type)
            {
                case  4:
                    $quests = new QuestList(array(['id', $entries]));
                    $quests->addRewardsToJscript($pageData);

                    $pageData['page']['questReward'] = $quests->getListviewData();
                    $pageData['page']['questParams'] = array(
                        'id'            => 'reward-from-quest',
                        'name'          => '$LANG.tab_rewardfrom',
                        'hiddenCols'    => "$['side']",
                        'visibleCols'   => "$['category']"
                    );
                    break;
                case 12:
                    $acvs = new AchievementList(array(['id', $entries]));
                    $acvs->addGlobalsToJscript($pageData);
                    $acvs->addRewardsToJscript($pageData);

                    $pageData['page']['acvReward'] = $acvs->getListviewData();
                    $pageData['page']['acvParams'] = array(
                        'id'            => 'reward-from-achievement',
                        'name'          => '$LANG.tab_rewardfrom',
                        'visibleCols'   => "$['category']",
                        'sort'          => "$['reqlevel', 'name']"
                    );
                    break;
                case 13:
                    // not displayed
            }
        }
        $pageData['title'] = ucFirst(trim(str_replace('%s', '', str_replace(',', '', $title->name[0]))));
        $pageData['path']  = '[0, 10, '.$title->getField('category').']';

        $smarty->saveCache($cacheKeyPage, $pageData);
    }
}

$smarty->updatePageVars(array(
    'title'     => $pageData['title']." - ".ucfirst(Lang::$game['title']),
    'path'      => $pageData['path'],
    'tab'       => 0,                                       // for g_initHeader($tab)
    'type'      => TYPE_TITLE,                              // 11:Titles
    'typeId'    => $id
));


$smarty->assign('community', CommunityContent::getAll(TYPE_TITLE, $id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('data', $pageData);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->display('title.tpl');

?>
