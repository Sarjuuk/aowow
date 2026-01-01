<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentsBaseResponse extends TemplateResponse implements ICache
{
    use TrListPage, TrCache;

    protected  int    $type        = Type::ENCHANTMENT;
    protected  int    $cacheType   = CACHE_TYPE_LIST_PAGE;

    protected  string $template    = 'enchantments';
    protected  string $pageName    = 'enchantments';
    protected ?int    $activeTab   = parent::TAB_DATABASE;
    protected  array  $breadcrumb  = [0, 101];

    protected  array  $scripts     = [[SC_JS_FILE, 'js/filters.js']];
    protected  array  $expectedGET = array(
        'filter' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Filter::PATTERN_PARAM]]
    );
    protected  array  $validCats   = [1, 2, 3, 4, 5, 6, 7, 8];

    public function __construct(string $rawParam)
    {
        $this->getCategoryFromUrl($rawParam);

        parent::__construct($rawParam);

        if ($this->category)
            $this->forward('?enchantments&filter=ty='.$this->category[0]);

        if ($this->category)
            $this->subCat = '='.implode('.', $this->category);

        $this->filter = new EnchantmentListFilter($this->_get['filter'] ?? '', ['parentCats' => $this->category]);
        if ($this->filter->shouldReload)
        {
            $_SESSION['error']['fi'] = $this->filter::class;
            $get = $this->filter->buildGETParam();
            $this->forward('?' . $this->pageName . $this->subCat . ($get ? '&filter=' . $get : ''));
        }
        $this->filterError = $this->filter->error;
    }

    protected function generate() : void
    {
        $this->h1 = Util::ucFirst(Lang::game('enchantments'));

        $conditions = [Listview::DEFAULT_SIZE];
        if (!User::isInGroup(U_GROUP_EMPLOYEE))
            $conditions[] = [['cuFlags', CUSTOM_EXCLUDE_FOR_LISTVIEW, '&'], 0];

        if ($_ = $this->filter->getConditions())
            $conditions[] = $_;


        /**************/
        /* Page Title */
        /**************/

        $fiForm = $this->filter->values;

        array_unshift($this->title, $this->h1);
        if (isset($fiForm['ty']) && count($fiForm['ty']) == 1 && $fiForm['ty'][0] > ENCHANTMENT_TYPE_NONE && $fiForm['ty'][0] <= ENCHANTMENT_TYPE_PRISMATIC_SOCKET)
            array_unshift($this->title, Lang::enchantment('types', $fiForm['ty'][0]));


        /*************/
        /* Menu Path */
        /*************/

        if (isset($fiForm['ty']) && count($fiForm['ty']) == 1)
            $this->breadcrumb[] = $fiForm['ty'][0];


        /****************/
        /* Main Content */
        /****************/

        $this->redButtons[BUTTON_WOWHEAD] = false;

        $tabData = array(
            'data' => [],
            'name' => Util::ucFirst(Lang::game('enchantments'))
        );

        $ench = new EnchantmentList($conditions, ['calcTotal' => true]);

        $tabData['data'] = $ench->getListviewData();
        $this->extendGlobalData($ench->getJSGlobals());

        $xCols = [];
        foreach (Stat::getFilterCriteriumIdFor() as $idx => $fiId)
            if (array_filter(array_column($tabData['data'], Stat::getJsonString($idx))))
                $xCols[] = $fiId;

        // some kind of declaration conflict going on here..., expects colId for WEAPON_DAMAGE_MAX but jsonString is WEAPON_DAMAGE
        if (array_filter(array_column($tabData['data'], 'dmg')))
            $xCols[] = Stat::getFilterCriteriumId(Stat::WEAPON_DAMAGE_MAX);

        if ($xCols)
            $this->filter->fiExtraCols = array_merge($this->filter->fiExtraCols, $xCols);

        if ($this->filter->fiExtraCols)
            $tabData['extraCols'] = '$fi_getExtraCols(fi_extraCols, 0, 0)';

        if (array_filter(array_column($tabData['data'], 'spells')))
            $tabData['visibleCols'] = ['trigger'];

        if (!$ench->hasSetFields('skillLine'))
            $tabData['hiddenCols'] = ['skill'];

        if ($ench->getMatches() > Listview::DEFAULT_SIZE)
        {
            $tabData['note'] = sprintf(Util::$tryFilteringString, 'LANG.lvnote_enchantmentsfound', $ench->getMatches(), Listview::DEFAULT_SIZE);
            $tabData['_truncated'] = 1;
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        $this->lvTabs->addListviewTab(new Listview($tabData, EnchantmentList::$brickFile, 'enchantment'));

        parent::generate();
    }
}

?>
