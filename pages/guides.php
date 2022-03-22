<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId ?:Category g_initPath()
//  tabid 6:Guides   g_initHeader()
class GuidesPage extends GenericPage
{
    use TrListPage;

    protected $type      = Type::GUIDE;
    protected $tpl       = 'list-page-generic';
    protected $path      = [6];
    protected $tabId     = 6;
    protected $mode      = CACHE_TYPE_PAGE;
    protected $validCats = [null, 1, 2, 3, 4, 5, 6, 7, 8, 9];

    private   $myGuides  = false;

    public function __construct($pageCall, $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageCall, $pageParam);

        if ($pageCall == 'my-guides')
        {
            if (!User::$id)
                $this->error();

            $this->name = Util::ucFirst(Lang::guide('myGuides'));
            $this->myGuides = true;
        }
        else
            $this->name = Util::ucFirst(Lang::game('guides'));
    }

    protected function generateContent()
    {
        $hCols = ['patch'];                                 // pointless: display date instead
        $vCols = [];
        $xCols = ['$Listview.extraCols.date'];              // ok

        if ($this->myGuides)
        {
            $conditions = [['userId', User::$id]];
            $hCols[] = 'author';
            $vCols[] = 'status';
        }
        else
        {
            $conditions = array(
                ['locale', User::$localeId],
                ['status', GUIDE_STATUS_ARCHIVED, '!'],     // never archived guides
                [
                    'OR',
                    ['status', GUIDE_STATUS_APPROVED],      // currently approved
                    ['rev', 0, '>']                         // has previously approved revision
                ]
            );
            if (isset($this->category[0]))
                $conditions[] = ['category', $this->category];
        }

        $data = [];
        $guides = new GuideList($conditions);
        if (!$guides->error)
            $data = array_values($guides->getListviewData());

        $tabData = array(
            'data'        => $data,
            'name'        => Util::ucFirst(Lang::game('guides')),
            'hiddenCols'  => $hCols,
            'visibleCols' => $vCols,
            'extraCols'   => $xCols
        );

        $this->lvTabs[] = [GuideList::$brickFile, $tabData];

        $this->redButtons = [BUTTON_GUIDE_NEW => User::$id && User::canComment()];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
        if (isset($this->category[0]))
            array_unshift($this->title, Lang::guide('category', $this->category[0]));

    }

    protected function generatePath()
    {
        if (isset($this->category[0]))
            $this->path[] = $this->category[0];
    }
}

?>
