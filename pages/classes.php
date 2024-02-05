<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 12: Class    g_initPath()
//  tabId  0: Database g_initHeader()
class ClassesPage extends GenericPage
{
    use TrListPage;

    protected $type          = Type::CHR_CLASS;
    protected $tpl           = 'list-page-generic';
    protected $path          = [0, 12];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name = Util::ucFirst(Lang::game('classes'));
    }

    protected function generateContent()
    {
        $classes = new CharClassList();
        if (!$classes->error)
            $this->lvTabs[] = [CharClassList::$brickFile, ['data' => array_values($classes->getListviewData())]];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('classes')));
    }

    protected function generatePath() {}
}

?>
