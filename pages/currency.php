<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 15: Currency g_initPath()
//  tabId  0: Database g_initHeader()
class CurrencyPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_CURRENCY;
    protected $typeId        = 0;
    protected $tpl           = 'detail-page-generic';
    protected $path          = [0, 15];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

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

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        if ($this->typeId == 103)                           // Arena Points
            $infobox[] = Lang::currency('cap').Lang::main('colon').'10\'000';
        else if ($this->typeId == 104)                      // Honor
            $infobox[] = Lang::currency('cap').Lang::main('colon').'75\'000';

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

                foreach ($lootTabs->iterate() as $tab)
                {
                    $this->lvTabs[] = array(
                        'file'   => $tab[0],
                        'data'   => $tab[1],
                        'params' => [
                            'name'        => $tab[2],
                            'id'          => $tab[3],
                            'extraCols'   => $tab[4] ? '$['.implode(', ', array_unique($tab[4])).']' : null,
                            'hiddenCols'  => $tab[5] ? '$['.implode(', ', array_unique($tab[5])).']' : null,
                            'visibleCols' => $tab[6] ? '$'. Util::toJSON( array_unique($tab[6]))     : null
                        ]
                    );
                }
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
                    $extraCols = ['Listview.extraCols.stock', "Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", 'Listview.extraCols.cost'];
                    $holidays  = [];

                    foreach ($sbData as $k => &$row)
                    {
                        $items  = [];
                        $tokens = [];
                        foreach ($vendors[$k] as $id => $qty)
                        {
                            if (is_string($id))
                                continue;

                            if ($id > 0)
                                $tokens[] = [$id, $qty];
                            else if ($id < 0)
                                $items[] = [-$id, $qty];
                        }

                        if ($vendors[$k]['event'])
                        {
                            if (count($extraCols) == 3)             // not already pushed
                                $extraCols[] = 'Listview.extraCols.condition';

                            $this->extendGlobalIds(TYPE_WORLDEVENT, $vendors[$k]['event']);
                            $row['condition'][0][$this->typeId][] = [[CND_ACTIVE_EVENT, $vendors[$k]['event']]];
                        }

                        $row['stock'] = $vendors[$k]['stock'];
                        $row['stack'] = $itemObj->getField('buyCount');
                        $row['cost']  = array(
                            $itemObj->getField('buyPrice'),
                            $items  ? $items  : null,
                            $tokens ? $tokens : null
                        );
                    }

                    $this->lvTabs[] = array(
                        'file'   => 'creature',
                        'data'   => $sbData,
                        'params' => [
                            'name'       => '$LANG.tab_soldby',
                            'id'         => 'sold-by-npc',
                            'extraCols'  => '$['.implode(', ', $extraCols).']',
                            'hiddenCols' => "$['level', 'type']"
                        ]
                    );
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

                if ($createdBy->hasSetFields(['reagent1']))
                    $visCols = ['reagents'];

                $this->lvTabs[] = array(
                    'file'   => 'spell',
                    'data'   => $createdBy->getListviewData(),
                    'params' => [
                        'name'        => '$LANG.tab_createdby',
                        'id'          => 'created-by',
                        'visibleCols' => isset($visCols) ? '$'.Util::toJSON($visCols) : null
                    ]
                );
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
                if ($boughtBy->getMatches() <= CFG_SQL_LIMIT_DEFAULT)
                    $n = null;

                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $boughtBy->getListviewData(ITEMINFO_VENDOR, [TYPE_CURRENCY => $this->typeId]),
                    'params' => [
                        'name'      => '$LANG.tab_currencyfor',
                        'id'        => 'currency-for',
                        'extraCols' => "$[Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')]",
                        'note'      => $n ? sprintf(Util::$filterResultString, $n) : null
                    ]
                );

                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }
    }
}

?>
