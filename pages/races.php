<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 13: Race     g_initPath()
//  tabId  0: Database g_initHeader()
class RacesPage extends GenericPage
{
    use ListPage;

    protected $type          = TYPE_CLASS;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 13];
    protected $tabId         = 0;
    protected $mode          = CACHETYPE_PAGE;

    public function __construct()
    {
        $this->name = Util::ucFirst(Lang::$game['races']);

        parent::__construct();
    }

    protected function generateContent()
    {
        $races = new CharRaceList(array(['side', 0, '!']));
        if (!$races->error)
        {
            $this->lvData[] = array(
                'file'   => 'race',
                'data'   => $races->getListviewData(),
                'params' => []
            );
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::$game['races']));
    }

    protected function generatePath() {}
}

?>
