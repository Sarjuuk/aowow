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
    protected $mode          = CACHETYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new CurrencyList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::$game['currency']);

        $this->name = $this->subject->getField('name', true);
    }

    protected function generatePath()
    {
        $this->path[] = $this->subject->getField('category');
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->subject->getField('name', true), Util::ucFirst(Lang::$game['currency']));
    }

    protected function generateContent()
    {
        $_itemId    = $this->subject->getField('itemId');

        /***********/
        /* Infobox */
        /**********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        if ($this->typeId == 103)                           // Arena Points
            $infobox[] = Lang::$currency['cap'].Lang::$main['colon'].'10\'000';
        else if ($this->typeId == 104)                      // Honor
            $infobox[] = Lang::$currency['cap'].Lang::$main['colon'].'75\'000';

        /****************/
        /* Main Content */
        /****************/

        $this->infobox    = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
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
                            'visibleCols' => $tab[6] ? '$'. json_encode(  array_unique($tab[6]))     : null
                        ]
                    );
                }
            }

            // tab: sold by
            $itemObj = new ItemList(array(['id', $_itemId]));
            if ($vendors = @$itemObj->getExtendedCost()[$_itemId])
            {
                $this->extendGlobalData($itemObj->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));

                $soldBy = new CreatureList(array(['id', array_keys($vendors)]));
                if (!$soldBy->error)
                {
                    $sbData    = $soldBy->getListviewData();
                    $extraCols = ['Listview.extraCols.stock', "Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack')", 'Listview.extraCols.cost'];
                    $holidays  = [];

                    foreach ($sbData as $k => &$row)
                    {
                        $this->subject = [];
                        $tokens   = [];
                        foreach ($vendors[$k] as $id => $qty)
                        {
                            if (is_string($id))
                                continue;

                            if ($id > 0)
                                $tokens[] = [$id, $qty];
                            else if ($id < 0)
                                $this->subject[] = [-$id, $qty];
                        }

                        if ($_ = $vendors[$k]['event'])
                        {
                            if (count($extraCols) == 3)             // not already pushed
                                $extraCols[] = 'Listview.extraCols.condition';

                            $holidays[$_] = 0;                      // applied as back ref.

                            $row['condition'][] = array(
                                'type'   => TYPE_WORLDEVENT,
                                'typeId' => &$holidays[$_],
                                'status' => 1
                            );
                        }

                        $row['stock'] = $vendors[$k]['stock'];
                        $row['stack'] = $itemObj->getField('buyCount');
                        $row['cost']  = array(
                            $itemObj->getField('buyPrice'),
                            $this->subject ? $this->subject : null,
                            $tokens   ? $tokens   : null
                        );
                    }

                    if ($holidays)
                    {
                        $hObj = new WorldEventList(array(['id', array_keys($holidays)]));
                        $this->extendGlobalData($hObj->getJSGlobals());
                        foreach ($hObj->iterate() as $id => $tpl)
                        {
                            if ($_ = $tpl['holidayId'])
                                $holidays[$tpl['eventBak']] = $_;
                            else
                                $holidays[-$id] = $id;
                        }
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

        // tab: created by (spell) [for items its handled in Util::getLootSource()]
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
                        'visibleCols' => isset($visCols) ? '$'.json_encode($visCols) : null
                    ]
                );
            }
        }

        // tab: currency for
        if ($this->typeId == 103)
        {
            $n = '?items&filter=cr=145;crs=1;crv=0';
            $w = 'iec.reqArenaPoints > 0';
        }
        else if ($this->typeId == 104)
        {
            $n = '?items&filter=cr=144;crs=1;crv=0';
            $w = 'iec.reqHonorPoints > 0';
        }
        else
        {
            $n = in_array($this->typeId, [42, 61, 81, 241, 121, 122, 123, 125, 126, 161, 201, 101, 102, 221, 301, 341]) ? '?items&filter=cr=158;crs='.$_itemId.';crv=0' : null;
            $w = 'iec.reqItemId1 = '.$_itemId.' OR iec.reqItemId2 = '.$_itemId.' OR iec.reqItemId3 = '.$_itemId.' OR iec.reqItemId4 = '.$_itemId.' OR iec.reqItemId5 = '.$_itemId;
        }

        $boughtBy = DB::Aowow()->selectCol('
            SELECT item FROM npc_vendor nv JOIN ?_itemextendedcost iec ON iec.id = nv.extendedCost WHERE '.$w.'
            UNION
            SELECT item FROM game_event_npc_vendor genv JOIN ?_itemextendedcost iec ON iec.id = genv.extendedCost WHERE '.$w
        );

        if ($boughtBy)
        {
            $boughtBy = new ItemList(array(['id', $boughtBy]));
            if (!$boughtBy->error)
            {
                $this->lvTabs[] = array(
                    'file'   => 'item',
                    'data'   => $boughtBy->getListviewData(ITEMINFO_VENDOR, [TYPE_CURRENCY => $this->typeId]),
                    'params' => [
                        'name'      => '$LANG.tab_currencyfor',
                        'id'        => 'currency-for',
                        'extraCols' => "$[Listview.funcBox.createSimpleCol('stack', 'stack', '10%', 'stack'), Listview.extraCols.cost]",
                        'note'      => $n ? '$$WH.sprintf(LANG.lvnote_filterresults, \''.$n.'\')' : null
                    ]
                );

                $this->extendGlobalData($boughtBy->getJSGlobals(GLOBALINFO_SELF | GLOBALINFO_RELATED));
            }
        }
    }
}

?>
