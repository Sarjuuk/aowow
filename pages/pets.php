<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 8: Pets     g_initPath()
//  tabid 0: Database g_initHeader()
class PetsPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_PET;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 8];
    protected $tabId         = 0;
    protected $mode          = CACHETYPE_PAGE;
    protected $validCats     = [1, 2, 3];

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);;

        parent::__construct();

        $this->name = Util::ucFirst(Lang::$game['pets']);
    }

    protected function generateContent()
    {
        $conditions = [];
        if ($this->category)
            $conditions[] = ['type', (int)$this->category[0]];

        $pets = new PetList($conditions);
        if (!$pets->error)
        {
            $this->extendGlobalData($pets->getJSGlobals(GLOBALINFO_RELATED));

            $params = ['visibleCols' => "$['abilities']"];
            if (!$pets->hasDiffFields(['type']))
                $params['hiddenCols'] = "$['type']";

            $this->lvTabs[] = array(
                'file'   => 'pet',
                'data'   => $pets->getListviewData(),
                'params' => $params
            );
        };
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::$game['pets']));
        if ($this->category)
            array_unshift($this->title, Lang::$pet['cat'][$this->category[0]]);
    }

    protected function generatePath()
    {
        if ($this->category)
            $this->path[] = $this->category[0];
    }
}

?>
