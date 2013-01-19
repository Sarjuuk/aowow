<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


require('includes/class.title.php');
require('includes/class.spell.php');
require('includes/class.achievement.php');
require('includes/class.item.php');
require('includes/class.quest.php');
require('includes/class.worldevent.php');
require('includes/class.community.php');

$Id = intval($pageParam);

if (!$smarty->loadCache(array('achievement', $Id), $pageData))
{
    $title = new Title($Id);
    if ($title->template)
    {
        $title->addSelfToJscript($pageData['gTitles']);

        $infobox = [];
        $colon   = User::$localeId == LOCALE_FR ? ' : ' : ': '; // Je suis un prick! <_<
        if ($title->template['side'] == 1)
            $infobox[] = Lang::$main['side'].$colon.'[span class=alliance-icon]'.Lang::$game['alliance'].'[/span]';
        else if ($title->template['side'] == 2)
            $infobox[] = Lang::$main['side'].$colon.'[span class=horde-icon]'.Lang::$game['horde'].'[/span]';
        else
            $infobox[] = Lang::$main['side'].$colon.Lang::$main['both'];

        if ($title->template['gender'])
            $infobox[] = Lang::$main['gender'].$colon.'[span class='.($title->template['gender'] == 2 ? 'female' : 'male').'-icon]'.Lang::$main['sex'][$title->template['gender']].'[/span]';

        if ($title->template['eventId'])
            $infobox[] = Lang::$game['eventShort'].$colon.'[url=?event='.$title->template['eventId'].']'.WorldEvent::getName($title->template['eventId']).'[/url]';

        $pageData = array(
            'page' => array(
                'name'      => $title->getHtmlizedName(),
                'id'        => $title->Id,
                'expansion' => Util::$expansionString[$title->template['expansion']]
            ),
            'infobox' => '[li][ul]'.implode('[/ul][ul]', $infobox).'[/ul][/li]',
        );

        foreach ($title->source as $type => $entries)
        {
            // todo: hidden-/visibleCols by actual use
            switch ($type)
            {
                case  4:
                    $quests = new QuestList(array(['id', $entries]));
                    $quests->addRewardsToJscript($pageData['gItems'], $pageData['gSpells'], $pageData['gTitles']);

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
                    $acvs->addSelfToJscript($pageData['gAchievements']);
                    $acvs->addRewardsToJscript($pageData['gItems'], $pageData['gTitles']);

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

		$smarty->saveCache(array('spell', $Id), $pageData);
    }
    else
    {
        $smarty->updatePageVars(array(
            'subject'   => ucfirst(Lang::$main['class']),
            'id'        => $Id,
            'notFound'  => sprintf(Lang::$main['pageNotFound'], Lang::$main['class']),
        ));
        $smarty->assign('lang', Lang::$main);
        $smarty->display('404.tpl');
        exit();
    }
}

$smarty->updatePageVars(array(
    'title'     => $pageData['title']." - ".Lang::$game['title'],
    'path'      => "[0, 10, ".$title->template['category']."]",
	'tab'       => 0,                                       // for g_initHeader($tab)
	'type'      => TYPEID_TITLE,                            // 11:Titles
	'typeId'    => $Id
));

$smarty->assign('community', CommunityContent::getAll(TYPEID_TITLE, $Id));  // comments, screenshots, videos
$smarty->assign('lang', array_merge(Lang::$main));
$smarty->assign('data', $pageData);
$smarty->assign('mysql', DB::Aowow()->getStatistics());
$smarty->display('title.tpl');

?>
