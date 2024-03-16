<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 11: Event    g_initPath()
//  tabId  0: Database g_initHeader()
class EventsPage extends GenericPage
{
    use TrListPage;

    private   $dependency    = [];

    protected $type          = Type::WORLDEVENT;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 11];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $validCats     = [0, 1, 2, 3];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('events'));
    }

    protected function generateContent()
    {
        $condition = [];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $condition[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
        {
            switch ($this->category[0])
            {
                case 0: $condition[] = ['e.holidayId', 0];          break;
                case 1: $condition[] = ['h.scheduleType', -1];      break;
                case 2: $condition[] = ['h.scheduleType', [0, 1]];  break;
                case 3: $condition[] = ['h.scheduleType', 2];       break;
            }
        }

        $events = new WorldEventList($condition);
        $this->extendGlobalData($events->getJSGlobals());

        foreach ($events->iterate() as $__)
            if ($d = $events->getField('requires'))
                $this->dependency[$events->id] = $d;

        $data = array_values($events->getListviewData());

        $this->lvTabs[] = [WorldEventList::$brickFile, ['data' => $data]];

        if ($_ = array_values(array_filter($data, function($x) {return $x['category'] > 0;})))
        {
            $this->lvTabs[] = ['calendar', array(
                'data'      => $_,
                'hideCount' => 1
            )];
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if ($this->category)
            array_unshift($this->title, Lang::event('category')[$this->category[0]]);
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }

    protected function postCache()
    {
        // recalculate dates with now()
        foreach ($this->lvTabs as &$views)
        {
            foreach ($views[1]['data'] as &$data)
            {
                // is a followUp-event
                if (!empty($this->dependency[$data['id']]))
                {
                    $data['startDate'] = $data['endDate'] = false;
                    unset($data['_date']);
                    continue;
                }

                $updated = WorldEventList::updateDates($data['_date']);
                unset($data['_date']);
                $data['startDate'] = $updated['start'] ? date(Util::$dateFormatInternal, $updated['start']) : false;
                $data['endDate']   = $updated['end']   ? date(Util::$dateFormatInternal, $updated['end'])   : false;
                $data['rec']       = $updated['rec'];
            }
        }
    }
}

?>
