<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    => Templated Page /w Listviews
*/


class SearchBaseResponse extends TemplateResponse implements ICache
{
    use TrCache, TrSearch;

    private const SEARCH_MODS_ALL = 0x0FFFFFFF;             // yeah im lazy, now what?

    protected  int    $cacheType   = CACHE_TYPE_SEARCH;

    protected  string $template    = 'search';
    protected  string $pageName    = 'search';
    protected ?int    $activeTab   = parent::TAB_DATABASE;

    protected  array  $expectedGET = array(
        'search' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    public string $invalidTerms = '';

    public function __construct(string $pageParam)
    {
        parent::__construct($pageParam);                    // just to set g_user and g_locale

        $this->query = $this->_get['search'];               // technically pageParam, but prepared

        if ($limit = Cfg::get('SQL_LIMIT_SEARCH'))
            $this->maxResults = $limit;

        $this->searchMask = Search::TYPE_REGULAR | self::SEARCH_MODS_ALL;

        $this->searchObj = new Search($this->query, $this->searchMask, $this->maxResults);
    }

    protected function generate() : void
    {
        if (!$this->query)                                  // empty search > goto home page
            $this->forward();

        $this->search = $this->query;                       // escaped by TemplateResponse

        if ($iv = $this->searchObj->invalid)
            $this->invalidTerms = implode(', ', Util::htmlEscape($iv));

        array_unshift($this->title, $this->search, Lang::main('search'));

        $this->redButtons[BUTTON_WOWHEAD] = true;
        $this->wowheadLink = sprintf(WOWHEAD_LINK, Lang::getLocale()->domain(), 'search=', Util::htmlEscape($this->query));

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], __forceTabs: true);

        $canRedirect = true;
        $redirectTo  = '';

        if ($this->searchObj->canPerform())
        {
            foreach ($this->searchObj->perform() as $lvData)
            {
                if ($lvData[1] == 'npc' || $lvData[1] == 'object')
                    $this->addDataLoader('zones');

                $this->lvTabs->addListviewTab(new Listview(...$lvData));

                // we already have a target > can't have more targets > no redirects
                if (($canRedirect && $redirectTo) || count($lvData[0]['data']) > 1)
                    $canRedirect = false;

                if ($canRedirect)                           // note - we are very lucky that in case of searches $template is identical to the typeString
                    $redirectTo = '?'.$lvData[1].'='.key($lvData[0]['data']);
            }
        }

        $this->extendGlobalData($this->searchObj->getJSGlobals());

        parent::generate();

        $this->result->registerDisplayHook('lvTabs', [self::class, 'tabsHook']);

        // this one stings..
        // we have to manually call saveCache, beacause normally it would be called AFTER the page is rendered..
        // .. which will not happen if we forward to somewhere
        // also we have to set a postCacheHook in this case that handles future forwards (gets called in display() so the currenct call is also covered)
        if ($canRedirect && $redirectTo)
        {
            $this->setOnCacheLoaded([self::class, 'onBeforeDisplay'], $redirectTo);
            $this->saveCache($this->result);
        }
    }

    // update dates to now()
    public static function tabsHook(Template\PageTemplate $pt, Tabs &$lvTabs) : void
    {
        foreach ($lvTabs->iterate() as &$listview)
            if ($listview instanceof Listview && $listview->getTemplate() == 'holiday')
                WorldEventList::updateListview($listview);
    }

    public static function onBeforeDisplay(Template\PageTemplate $pt, string $url) : never
    {
        header('Location: '.$url, true, 302);               // we no longer have access to BaseResponse .. so thats fun
        exit();
    }
}

?>
