<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class LatestscreenshotsBaseResponse extends TemplateResponse
{
    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'latest-screenshots';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 8, 3];              // Tools > Util > Latest Screenshots

    protected function generate() : void
    {
        $this->h1     = Lang::main('utilities', 3);
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

        $data = CommunityContent::getScreenshots(resultLimit: Listview::DEFAULT_SIZE);
        $this->lvTabs->addListviewTab(new Listview(['data' => $data], 'screenshot'));

        parent::generate();
    }
}

?>
