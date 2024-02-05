<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 103: Mail     g_initPath()
//  tabid   0: Database g_initHeader()
class MailsPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::MAIL;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 103];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('mails'));
    }

    protected function generateContent()
    {
        $tabData = [];
        $mails = new MailList();
        if (!$mails->error)
            $tabData['data'] = array_values($mails->getListviewData());

        $this->extendGlobalData($mails->getJsGlobals());

        $this->lvTabs[] = [MailList::$brickFile, $tabData, 'mail'];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() { }
}

?>
