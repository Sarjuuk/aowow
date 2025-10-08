<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CurrencyBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'detail-page-generic';
    protected  string $pageName   = 'currency';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 15];

    public int $type   = Type::CURRENCY;
    public int $typeId = 0;

    private CurrencyList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }


    protected function generate() : void
    {
        $this->subject = new CurrencyList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('currency'), Lang::currency('notFound'));

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_relItemId = $this->subject->getField('itemId');


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('currency')));


        /*************/
        /* Menu Path */
        /*************/

        $this->breadcrumb[] = $this->subject->getField('category');


        /***********/
        /* Infobox */
        /**********/

        $infobox = Lang::getInfoBoxForFlags(intval($this->subject->getField('cuFlags')));

        // cap
        if ($_ = $this->subject->getField('cap'))
            $infobox[] = Lang::currency('cap').Lang::nf($_);

        // id
        $infobox[] = Lang::currency('id') . $this->typeId;

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(Lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(Type::ICON, $_);
        }

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        $hi = $this->subject->getJSGlobals()[Type::CURRENCY][$this->typeId]['icon'];
        if ($hi[0] == $hi[1])
            unset($hi[1]);

        $this->headIcons  = $hi;
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        );

        if ($_ = $this->subject->getField('description', true))
            $this->extraText = new Markup($_, ['dbpage' => true, 'allow' => Markup::CLASS_ADMIN], 'text-generic');


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        if ($this->typeId != CURRENCY_HONOR_POINTS && $this->typeId != CURRENCY_ARENA_POINTS)
        {
            // tabs: this currency is contained in..
            $lootTabs = new Loot();

            if ($lootTabs->getByItem($_relItemId))
            {
                $this->extendGlobalData($lootTabs->jsGlobals);

                foreach ($lootTabs->iterate() as [$template, $tabData])
                {
                    if ($template == 'npc' || $template == 'object')
                        $this->addDataLoader('zones');

                    $this->lvTabs->addListviewTab(new Listview($tabData, $template));
                }
            }

            // tab: sold by
            $itemObj = new ItemList(array(['id', $_relItemId]));
            if (!empty($itemObj->getExtendedCost()[$_relItemId]))
            {
                $vendors = $itemObj->getExtendedCost()[$_relItemId];
                $this->extendGlobalData($itemObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $soldBy = new CreatureList(array(['id', array_keys($vendors)]));
                if (!$soldBy->error)
                {
                    $sbData    = $soldBy->getListviewData();
                    $extraCols = ['$Listview.extraCols.stock', "\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost', '$Listview.extraCols.condition'];
                    foreach ($sbData as $k => &$row)
                    {
                        $items  = [];
                        $tokens = [];
                        // note: can only display one entry per row, so only use first entry of each vendor
                        foreach ($vendors[$k][0] as $id => $qty)
                        {
                            if (is_string($id))
                                continue;

                            if ($id > 0)
                                $tokens[] = [$id, $qty];
                            else if ($id < 0)
                                $items[] = [-$id, $qty];
                        }

                        if ($e = $vendors[$k][0]['event'])
                            if (Conditions::extendListviewRow($row, Conditions::SRC_NONE, $k, [Conditions::ACTIVE_EVENT, $e]))
                                $this->extendGlobalIds(Type::WORLDEVENT, $e);

                        $row['stock'] = $vendors[$k][0]['stock'];
                        $row['stack'] = $itemObj->getField('buyCount');
                        $row['cost']  = array(
                            $itemObj->getField('buyPrice'),
                            $items  ?: null,
                            $tokens ?: null
                        );
                    }

                    // no conditions > remove conditions column
                    if (!array_column($sbData, 'condition'))
                        array_pop($extraCols);

                    $this->addDataLoader('zones');
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'       => $sbData,
                        'name'       => '$LANG.tab_soldby',
                        'id'         => 'sold-by-npc',
                        'extraCols'  => $extraCols,
                        'hiddenCols' => ['level', 'type']
                    ), CreatureList::$brickFile));
                }
            }
        }

        // tab: created by (spell) [for items its handled in Loot::getByContainer()]
        if ($this->typeId == CURRENCY_HONOR_POINTS)
        {
            $createdBy = new SpellList(array(['effect1Id', SPELL_EFFECT_ADD_HONOR], ['effect2Id', SPELL_EFFECT_ADD_HONOR], ['effect3Id', SPELL_EFFECT_ADD_HONOR], 'OR'));
            if (!$createdBy->error)
            {
                $this->extendGlobalData($createdBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $tabData = array(
                    'data' => $createdBy->getListviewData(),
                    'name' => '$LANG.tab_createdby',
                    'id'   => 'created-by',
                );

                if ($createdBy->hasSetFields('reagent1', 'reagent2', 'reagent3', 'reagent4', 'reagent5', 'reagent6', 'reagent7', 'reagent8'))
                    $tabData['visibleCols'] = ['reagents'];

                $this->lvTabs->addListviewTab(new Listview($tabData, SpellList::$brickFile));
            }
        }

        // tab: currency for
        $n = $w = null;
        if ($this->typeId == CURRENCY_ARENA_POINTS)
        {
            $n = '?items&filter=cr=145;crs=1;crv=0';
            $w = '`reqArenaPoints` > 0';
        }
        else if ($this->typeId == CURRENCY_HONOR_POINTS)
        {
            $n = '?items&filter=cr=144;crs=1;crv=0';
            $w = '`reqHonorPoints` > 0';
        }
        else
            $w = '`reqItemId1` = '.$_relItemId.' OR `reqItemId2` = '.$_relItemId.' OR `reqItemId3` = '.$_relItemId.' OR `reqItemId4` = '.$_relItemId.' OR `reqItemId5` = '.$_relItemId;

        if (!$n && !is_null(ItemListFilter::getCriteriaIndex(158, $_relItemId)))
            $n = '?items&filter=cr=158;crs='.$_relItemId.';crv=0';

        $xCosts   = DB::Aowow()->selectCol('SELECT `id` FROM ?_itemextendedcost WHERE '.$w);
        $boughtBy = $xCosts ? DB::World()->selectCol('SELECT `item` FROM npc_vendor WHERE `extendedCost` IN (?a) UNION SELECT `item` FROM game_event_npc_vendor WHERE `extendedCost` IN (?a)', $xCosts, $xCosts) : [];
        if ($boughtBy)
        {
            $boughtBy = new ItemList(array(['id', $boughtBy]));
            if (!$boughtBy->error)
            {
                $tabData = array(
                    'data'      => $boughtBy->getListviewData(ITEMINFO_VENDOR, [Type::CURRENCY => $this->typeId]),
                    'name'      => '$LANG.tab_currencyfor',
                    'id'        => 'currency-for',
                    'extraCols' => ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost']
                );

                if ($n)
                    $tabData['note'] = sprintf(Util::$filterResultString, $n);

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemList::$brickFile));

                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }

        parent::generate();
    }
}

?>
