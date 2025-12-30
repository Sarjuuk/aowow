<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type       = Type::PET;
    protected  int    $cacheType  = CACHE_TYPE_LIST_PAGE;

    protected  string $template   = 'list-page-generic';
    protected  string $pageName   = 'pets';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 8];

    protected  array  $validCats  = [PET_TALENT_TYPE_FEROCITY, PET_TALENT_TYPE_TENACITY, PET_TALENT_TYPE_CUNNING];

    public function __construct(string $pageParam)
    {
        $this->getCategoryFromUrl($pageParam);

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('pets'));


        /*************/
        /* Menu Path */
        /*************/

        if ($this->category)
            $this->breadcrumb[] = $this->category[0];


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1);
        if ($this->category)
            array_unshift($this->title, Lang::pet('cat', $this->category[0]));


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = true;

        $conditions = [Listview::DEFAULT_SIZE];

        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($this->category)
            $conditions[] = ['type', $this->category[0]];

        $tabData = [];
        $pets = new PetList($conditions);
        if (!$pets->error)
        {
            $this->extendGlobalData($pets->getJSGlobals(GLOBALINFO_RELATED));

            $tabData = array(
                'data'            => $pets->getListviewData(),
                'visibleCols'     => ['abilities'],
                'computeDataFunc' => '$_',
                'hiddenCols'      => !$pets->hasDiffFields('type') ? ['type'] : null
            );
        };

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, PetList::$brickFile, 'petFoodCol'));

        parent::generate();
    }
}

?>
