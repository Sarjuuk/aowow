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

    private const /* int   */ SEARCH_MODS_ALL = 0x0FFFFFFF; // yeah im lazy, now what?
    private const /* array */ SPECIAL_TOKENS  = ['mankrik', 'wife'];

    protected  int    $cacheType   = CACHE_TYPE_SEARCH;

    protected  string $template    = 'search';
    protected  string $pageName    = 'search';
    protected ?int    $activeTab   = parent::TAB_DATABASE;

    protected  array  $expectedGET = array(
        'search' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    public string $invalidTerms = '';

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);                     // just to set g_user and g_locale

        $this->query = $this->_get['search'];               // technically rawParam, but prepared

        if (preg_match('/^ *(\w+) +(-?\d+) *$/i', $this->query, $m) && Type::getIndexFrom(Type::IDX_FILE_STR, $m[1]))
            $this->forward('?'.$m[1].'='.$m[2]);

        $this->searchMask = Search::TYPE_REGULAR | self::SEARCH_MODS_ALL;

        $this->searchObj = new Search($this->query, $this->searchMask);
    }

    protected function generate() : void
    {
        if (!$this->query)                                  // empty search > goto home page
            $this->forward();

        // mankrik's beaten corpse easteregg
        $i = 0;
        foreach (self::SPECIAL_TOKENS as $tok)
            if (stripos($this->query, $tok) !== false)
                $i++;

        if ($i == count(self::SPECIAL_TOKENS))
            $this->forward('?maps=17:493504246248246262246275246287246302246314220234233234242234262234251234272234290236291248291260291277291289291310291322299277310277319277332254332240332267332277332318334300369236370275371310410312398312387306379306369295370258378238388236380266393266401266398234411234430236430279431316431297431254441233459234466246461258455269445281455295466306475316505229503304503287503273505256516234511310520314549312515267527267534267528233540233549233430267505240579330579347579366572390562403549419519407588328612374612353607388602405595425589436575454582446531413538421569491514487527493549491511473512458516425558491601326585481567467615335608322577475531314538314452229512438536493577491');

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
