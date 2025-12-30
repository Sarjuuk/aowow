<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LatestcommentsBaseResponse extends TemplateResponse
{
    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'latest-comments';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 8, 2];              // Tools > Util > Latest Comments

    protected function generate() : void
    {
        $this->h1     = Lang::main('utilities', 2);
        $this->h1Link = '?'.$this->pageName.'&rss' . (Lang::getLocale()->value ? '&locale='.Lang::getLocale()->value : '');
        $this->rss    = Cfg::get('HOST_URL').'/?' . $this->pageName . '&amp;rss' . (Lang::getLocale()->value ? '&amp;locale='.Lang::getLocale()->value : '');


        /*********/
        /* Title */
        /*********/

        array_unshift($this->title, $this->h1);


        /****************/
        /* Main Content */
        /****************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $comments = CommunityContent::getCommentPreviews(['comments' => true, 'replies' => false], resultLimit: Listview::DEFAULT_SIZE);
        $this->lvTabs->addListviewTab(new Listview(['data' => $comments], 'commentpreview'));

        $replies = CommunityContent::getCommentPreviews(['comments' => false, 'replies' => true], resultLimit: Listview::DEFAULT_SIZE);
        $this->lvTabs->addListviewTab(new Listview(['data' => $replies], 'replypreview'));

        parent::generate();
    }
}

?>
