<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 15: Currency g_initPath()
//  tabId  0: Database g_initHeader()
class CurrencyPage extends GenericPage
{
    use TrDetailPage;

    protected $type          = TYPE_CURRENCY;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 15];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    private   $powerTpl      = '$WowheadPower.registerCurrency(%d, %d, %s);';

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new CurrencyList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('currency'), Lang::currency('notFound'));

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('category');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::game('currency')));
    }

    protected function generateContent()
    {
        $this->addJS('?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $_itemId = $this->subject->getField('itemId');

        /***********/
        /* Infobox */
        /**********/

        $infobox = Lang::getInfoBoxForFlags(intval($this->subject->getField('cuFlags')));

        // cap
        if ($_ = $this->subject->getField('cap'))
            $infobox[] = Lang::currency('cap').Lang::main('colon').Lang::nf($_);

        // icon
        if ($_ = $this->subject->getField('iconId'))
        {
            $infobox[] = Util::ucFirst(lang::game('icon')).Lang::main('colon').'[icondb='.$_.' name=true]';
            $this->extendGlobalIds(TYPE_ICON, $_);
        }

        /****************/
        /* Main Content */
        /****************/

        $this->infobox    = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->name       = $this->subject->getField('name', true);
        $this->headIcons  = $this->typeId == 104 ? ['inv_bannerpvp_02', 'inv_bannerpvp_01'] : [$this->subject->getField('iconString')];
        $this->redButtons = array(
            BUTTON_WOWHEAD => true,
            BUTTON_LINKS   => true
        );

        if ($_ = $this->subject->getField('description', true))
            $this->extraText = $_;

        /**************/
        /* Extra Tabs */
        /**************/

        if ($this->typeId != 103 && $this->typeId != 104)   // honor && arena points are not handled as items
        {
            // tabs: this currency is contained in..
            $lootTabs = new Loot();

            if ($lootTabs->getByItem($_itemId))
            {
                $this->extendGlobalData($lootTabs->jsGlobals);

                foreach ($lootTabs->iterate() as [$file, $tabData])
                    $this->lvTabs[] = [$file, $tabData];
            }

            // tab: sold by
            $itemObj = new ItemList(array(['id', $_itemId]));
            if (!empty($itemObj->getExtendedCost()[$_itemId]))
            {
                $vendors = $itemObj->getExtendedCost()[$_itemId];
                $this->extendGlobalData($itemObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $soldBy = new CreatureList(array(['id', array_keys($vendors)]));
                if (!$soldBy->error)
                {
                    $sbData    = $soldBy->getListviewData();
                    $extraCols = ['$Listview.extraCols.stock', "\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'];
                    $holidays  = [];

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

                        if ($vendors[$k][0]['event'])
                        {
                            if (count($extraCols) == 3)             // not already pushed
                                $extraCols[] = '$Listview.extraCols.condition';

                            $this->extendGlobalIds(TYPE_WORLDEVENT, $vendors[$k][0]['event']);
                            $row['condition'][0][$this->typeId][] = [[CND_ACTIVE_EVENT, $vendors[$k][0]['event']]];
                        }

                        $row['stock'] = $vendors[$k][0]['stock'];
                        $row['stack'] = $itemObj->getField('buyCount');
                        $row['cost']  = array(
                            $itemObj->getField('buyPrice'),
                            $items  ? $items  : null,
                            $tokens ? $tokens : null
                        );
                    }

                    $this->lvTabs[] = ['creature', array(
                        'data'       => array_values($sbData),
                        'name'       => '$LANG.tab_soldby',
                        'id'         => 'sold-by-npc',
                        'extraCols'  => $extraCols,
                        'hiddenCols' => ['level', 'type']
                    )];
                }
            }
        }

        // tab: created by (spell) [for items its handled in Loot::getByContainer()]
        if ($this->typeId == 104)
        {
            $createdBy = new SpellList(array(['effect1Id', 45], ['effect2Id', 45], ['effect3Id', 45], 'OR'));
            if (!$createdBy->error)
            {
                $this->extendGlobalData($createdBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $tabData = array(
                    'data' => array_values($createdBy->getListviewData()),
                    'name' => '$LANG.tab_createdby',
                    'id'   => 'created-by',
                );

                if ($createdBy->hasSetFields(['reagent1']))
                    $tabData['visibleCols'] = ['reagents'];

                $this->lvTabs[] = ['spell', $tabData];
            }
        }

        // tab: currency for
        if ($this->typeId == 103)
        {
            $n = '?items&filter=cr=145;crs=1;crv=0';
            $w = 'reqArenaPoints > 0';
        }
        else if ($this->typeId == 104)
        {
            $n = '?items&filter=cr=144;crs=1;crv=0';
            $w = 'reqHonorPoints > 0';
        }
        else
        {
            $n = in_array($this->typeId, [42, 61, 81, 241, 121, 122, 123, 125, 126, 161, 201, 101, 102, 221, 301, 341]) ? '?items&filter=cr=158;crs='.$_itemId.';crv=0' : null;
            $w = 'reqItemId1 = '.$_itemId.' OR reqItemId2 = '.$_itemId.' OR reqItemId3 = '.$_itemId.' OR reqItemId4 = '.$_itemId.' OR reqItemId5 = '.$_itemId;
        }

        $xCosts   = DB::Aowow()->selectCol('SELECT id FROM ?_itemextendedcost WHERE '.$w);
        $boughtBy = $xCosts ? DB::World()->selectCol('SELECT item FROM npc_vendor WHERE extendedCost IN (?a) UNION SELECT item FROM game_event_npc_vendor WHERE extendedCost IN (?a)', $xCosts, $xCosts) : [];
        if ($boughtBy)
        {
            $boughtBy = new ItemList(array(['id', $boughtBy]));
            if (!$boughtBy->error)
            {
                $tabData = array(
                    'data'      => array_values($boughtBy->getListviewData(ITEMINFO_VENDOR, [TYPE_CURRENCY => $this->typeId])),
                    'name'      => '$LANG.tab_currencyfor',
                    'id'        => 'currency-for',
                    'extraCols' => ["\$Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", '$Listview.extraCols.cost'],
                );

                if ($boughtBy->getMatches() > CFG_SQL_LIMIT_DEFAULT)
                    $tabData['note'] = sprintf(Util::$filterResultString, $n);

                $this->lvTabs[] = ['item', $tabData];

                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }
    }

    protected function generateTooltip()
    {
        $power = new StdClass();
        if (!$this->subject->error)
        {
            $power->{'name_'.User::$localeString}    = $this->subject->getField('name', true);
            $power->icon                             = rawurlencode($this->subject->getField('iconString', true, true));
            $power->{'tooltip_'.User::$localeString} = $this->subject->renderTooltip();
        }

        return sprintf($this->powerTpl, $this->typeId, User::$localeId, Util::toJSON($power, JSON_AOWOW_POWER));
    }
}

?>
