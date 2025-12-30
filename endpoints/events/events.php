<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EventsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::WORLDEVENT;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'events';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 11];

    protected array  $validCats  = [0, 1, 2, 3];

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucWords(Lang::game('events'));


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::event('category')[$this->category[0]]);


        /*************/
        /* Menu Path */
        /*************/

        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        $condition = [Listview::DEFAULT_SIZE];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $condition[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $condition[] = match ($this->category[0])
            {
                1       => ['h.scheduleType', -1],
                2       => ['h.scheduleType', [0, 1]],
                3       => ['h.scheduleType', 2],
                default => ['e.holidayId', 0]               // also cat 0
            };

        $events = new WorldEventList($condition);
        $this->extendGlobalData($events->getJSGlobals());

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview(['data' => $events->getListviewData()], WorldEventList::$brickFile));

        if ($_ = array_filter($events->getListviewData(), fn($x) => $x['category'] > 0))
            $this->lvTabs->addListviewTab(new Listview(['data' => $_, 'hideCount' => 1], 'calendar'));

        parent::generate();

        $this->result->registerDisplayHook('lvTabs', [self::class, 'tabsHook']);
    }

    // recalculate dates with now()
    public static function tabsHook(Template\PageTemplate &$pt, Tabs &$lvTabs) : void
    {
        foreach ($lvTabs->iterate() as &$listview)
            if (is_object($listview) && ($listview?->getTemplate() == 'holiday' || $listview?->getTemplate() == 'holidaycal'))
                WorldEventList::updateListview($listview);
    }
}

?>
