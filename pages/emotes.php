<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 100: Emotes   g_initPath()
//  tabid   0: Database g_initHeader()
class EmotesPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::EMOTE;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 100];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('emotes'));
    }

    protected function generateContent()
    {
        $tabData = array(
            'data' => array_values((new EmoteList())->getListviewData()),
            'name' => Util::ucFirst(Lang::game('emotes'))
        );

        $this->lvTabs[] = ['emote', $tabData, 'emote'];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() { }
}

?>
