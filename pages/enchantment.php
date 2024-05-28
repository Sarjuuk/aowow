<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 101: Enchantment g_initPath()
//  tabId   0: Database    g_initHeader()
class EnchantmentPage extends GenericPage
{
    use TrDetailPage;

    protected $effects       = [];
    protected $activation    = [];

    protected $type          = Type::ENCHANTMENT;
    protected $typeId        = 0;
    protected $tpl           = 'enchantment';
    protected $path          = [0, 101];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        $this->typeId = intVal($id);

        $this->subject = new EnchantmentList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::game('enchantment'), Lang::enchantment('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->name = Util::ucFirst($this->subject->getField('name', true));
    }

    private function getDistinctType()
    {
        $type = 0;
        for ($i = 1; $i < 4; $i++)
        {
            if ($_ = $this->subject->getField('type'.$i))
            {
                if ($type)                                  // already set
                    return 0;
                else
                    $type = $_;
            }
        }

        return $type;
    }

    protected function generateContent()
    {
        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // reqLevel
        if ($_ = $this->subject->getField('requiredLevel'))
            $infobox[] = sprintf(Lang::game('reqLevel'), $_);

        // reqskill
        if ($_ = $this->subject->getField('skillLine'))
        {
            $this->extendGlobalIds(Type::SKILL, $_);

            $foo = sprintf(Lang::game('requires'), '&nbsp;[skill='.$_.']');
            if ($_ = $this->subject->getField('skillLevel'))
                $foo .= ' ('.$_.')';

            $infobox[] = $foo;
        }


        /****************/
        /* Main Content */
        /****************/


        $this->infobox = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->redButtons = array(
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_WOWHEAD => false
        );

        $this->effects = [];
        // 3 effects
        for ($i = 1; $i < 4; $i++)
        {
            $_ty  = $this->subject->getField('type'.$i);
            $_qty = $this->subject->getField('amount'.$i);
            $_obj = $this->subject->getField('object'.$i);

            switch ($_ty)
            {
                case 1:
                case 3:
                case 7:
                    $sArr = $this->subject->getField('spells')[$i];
                    $spl  = $this->subject->getRelSpell($sArr[0]);
                    $this->effects[$i]['name']  = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'Type: '.$_ty, Lang::item('trigger', $sArr[1])) : Lang::item('trigger', $sArr[1]);
                    $this->effects[$i]['proc']  = $sArr[3];
                    $this->effects[$i]['value'] = $_qty ?: null;
                    $this->effects[$i]['icon']  = array(
                        'name'  => !$spl ? Util::ucFirst(Lang::game('spell')).' #'.$sArr[0] : Util::localizedString($spl, 'name'),
                        'id'    => $sArr[0],
                        'count' => $sArr[2]
                    );
                    break;
                case 5:
                    if ($_obj < 2)                       // [mana, health] are on [0, 1] respectively and are expected on [1, 2] ..
                        $_obj++;                         // 0 is weaponDmg .. ehh .. i messed up somewhere

                    $this->effects[$i]['tip'] = [$_obj, Game::$itemMods[$_obj]];
                    // DO NOT BREAK!
                case 2:
                case 6:
                case 8:
                case 4:
                    $this->effects[$i]['name']  = User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'Type: '.$_ty, Lang::enchantment('types', $_ty)) : Lang::enchantment('types', $_ty);
                    $this->effects[$i]['value'] = $_qty;
                    if ($_ty == 4)
                        $this->effects[$i]['name'] .= Lang::main('colon').'('.(User::isInGroup(U_GROUP_EMPLOYEE) ? sprintf(Util::$dfnString, 'Object: '.$_obj, Lang::getMagicSchools(1 << $_obj)) : Lang::getMagicSchools(1 << $_obj)).')';
            }
        }

        // activation conditions
        if ($_ = $this->subject->getField('conditionId'))
        {
            $x = '';

            if ($gemCnd = DB::Aowow()->selectRow('SELECT * FROM ?_itemenchantmentcondition WHERE id = ?d', $_))
            {
                for ($i = 1; $i < 6; $i++)
                {
                    if (!$gemCnd['color'.$i])
                        continue;

                    $fiColors = function ($idx)
                    {
                        $foo = '';
                        switch ($idx)
                        {
                            case 2: $foo = '0:3:5'; break;  // red
                            case 3: $foo = '2:4:5'; break;  // yellow
                            case 4: $foo = '1:3:4'; break;  // blue
                        }

                        return $foo;
                    };

                    $bLink = $gemCnd['color'.$i]    ? '<a class="tip" href="?items=3&filter=ty='.$fiColors($gemCnd['color'.$i]).'">'.Lang::item('gemColors', $gemCnd['color'.$i] - 1).'</a>'       : '';
                    $cLink = $gemCnd['cmpColor'.$i] ? '<a class="tip" href="?items=3&filter=ty='.$fiColors($gemCnd['cmpColor'.$i]).'">'.Lang::item('gemColors', $gemCnd['cmpColor'.$i] - 1).'</a>' : '';

                    switch ($gemCnd['comparator'.$i])
                    {
                        case 2:                             // requires less <color> than (<value> || <comparecolor>) gems
                        case 5:                             // requires at least <color> than (<value> || <comparecolor>) gems
                            $sp = (int)$gemCnd['value'.$i] > 1;
                            $x .= '<span class="q0">'.Lang::achievement('reqNumCrt').' '.Lang::item('gemConditions', $gemCnd['comparator'.$i], [$gemCnd['value'.$i], $bLink]).'</span><br />';
                            break;
                        case 3:                             // requires more <color> than (<value> || <comparecolor>) gems
                            $link = '<a href="?items=3&filter=ty='.$fiColors($gemCnd['cmpColor'.$i]).'">'.Lang::item('gemColors', $gemCnd['cmpColor'.$i] - 1).'</a>';
                            $x .= '<span class="q0">'.Lang::achievement('reqNumCrt').' '.Lang::item('gemConditions', $gemCnd['comparator'.$i], [$bLink, $cLink]).'</span><br />';
                            break;
                    }
                }
            }

            $this->activation = $x;
        }

        /**************/
        /* Extra Tabs */
        /**************/

        // used by gem
        $gemList = new ItemList(array(['gemEnchantmentId', $this->typeId]));
        if (!$gemList->error)
        {
            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data' => array_values($gemList->getListviewData()),
                'name' => '$LANG.tab_usedby + \' \' + LANG.gems',
                'id'   => 'used-by-gem',
            )];

            $this->extendGlobalData($gemList->getJsGlobals());
        }

        // used by socket bonus
        $socketsList = new ItemList(array(['socketBonus', $this->typeId]));
        if (!$socketsList->error)
        {
            $this->lvTabs[] = [ItemList::$brickFile, array(
                'data' => array_values($socketsList->getListviewData()),
                'name' => '$LANG.tab_socketbonus',
                'id'   => 'used-by-socketbonus',
            )];

            $this->extendGlobalData($socketsList->getJsGlobals());
        }

        // used by spell
        // used by useItem
        $cnd = array(
            'OR',
            ['AND', ['effect1Id', [53, 54, 156, 92]], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', [53, 54, 156, 92]], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', [53, 54, 156, 92]], ['effect3MiscValue', $this->typeId]],
        );
        $spellList = new SpellList($cnd);
        if (!$spellList->error)
        {
            $spellData = $spellList->getListviewData();
            $this->extendGlobalData($spellList->getJsGlobals());

            $spellIds = $spellList->getFoundIDs();
            $conditions = array(
                'OR',
                ['AND', ['spellTrigger1', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId1', $spellIds]],
                ['AND', ['spellTrigger2', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId2', $spellIds]],
                ['AND', ['spellTrigger3', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId3', $spellIds]],
                ['AND', ['spellTrigger4', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId4', $spellIds]],
                ['AND', ['spellTrigger5', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId5', $spellIds]]
            );

            $ubItems = new ItemList($conditions);
            if (!$ubItems->error)
            {
                $this->lvTabs[] = [ItemList::$brickFile, array(
                    'data' => array_values($ubItems->getListviewData()),
                    'name' => '$LANG.tab_usedby + \' \' + LANG.types[3][0]',
                    'id'   => 'used-by-item',
                )];

                $this->extendGlobalData($ubItems->getJSGlobals(GLOBALINFO_SELF));
            }

            // remove found spells if they are used by an item
            if (!$ubItems->error)
            {
                foreach ($spellList->iterate() as $sId => $__)
                {
                    // if Perm. Enchantment has a createItem its a Scroll of Enchantment (display both)
                    for ($i = 1; $i < 4; $i++)
                        if ($spellList->getField('effect'.$i.'Id') == 53 && $spellList->getField('effect'.$i.'CreateItemId'))
                            continue 2;

                    foreach ($ubItems->iterate() as $__)
                    {
                        for ($i = 1; $i < 6; $i++)
                        {
                            if ($ubItems->getField('spellId'.$i) == $sId)
                            {
                                unset($spellData[$sId]);
                                break 2;
                            }
                        }
                    }
                }
            }

            $this->lvTabs[] = [SpellList::$brickFile, array(
                'data' => array_values($spellData),
                'name' => '$LANG.tab_usedby + \' \' + LANG.types[6][0]',
                'id'   => 'used-by-spell',
            )];
        }

        // used by randomAttrItem
        $ire = DB::Aowow()->select(
            'SELECT *, ABS(id) AS ARRAY_KEY FROM ?_itemrandomenchant WHERE enchantId1 = ?d OR enchantId2 = ?d OR enchantId3 = ?d OR enchantId4 = ?d OR enchantId5 = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );
        if ($ire)
        {
            if ($iet = DB::World()->select('SELECT entry AS ARRAY_KEY, ench, chance FROM item_enchantment_template WHERE ench IN (?a)', array_keys($ire)))
            {
                $randIds = [];                                  // transform back to signed format
                foreach ($iet as $tplId => $data)
                    $randIds[$ire[$data['ench']]['id'] > 0 ? $tplId : -$tplId] = $ire[$data['ench']]['id'];

                $randItems = new ItemList(array(Cfg::get('SQL_LIMIT_NONE'), ['randomEnchant', array_keys($randIds)]));
                if (!$randItems->error)
                {
                    $data = $randItems->getListviewData();
                    foreach ($randItems->iterate() as $iId => $__)
                    {
                        $re = $randItems->getField('randomEnchant');

                        $data[$iId]['percent'] = $iet[abs($re)]['chance'];
                        $data[$iId]['count']   = 1;         // expected by js or the pct-col becomes unsortable
                        $data[$iId]['rel']     = 'rand='.$ire[$iet[abs($re)]['ench']]['id'];
                        $data[$iId]['name']   .= ' '.Util::localizedString($ire[$iet[abs($re)]['ench']], 'name');
                    }

                    $this->lvTabs[] = [ItemList::$brickFile, array(
                        'data'      => array_values($data),
                        'id'        => 'used-by-rand',
                        'name'      => '$LANG.tab_usedby + \' \' + \''.Lang::item('_rndEnchants').'\'',
                        'extraCols' => ['$Listview.extraCols.percent']
                    )];

                    $this->extendGlobalData($randItems->getJSGlobals(GLOBALINFO_SELF));
                }
            }
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('enchantment')));
    }

    protected function generatePath()
    {
        if ($_ = $this->getDistinctType())
            $this->path[] = $_;
    }
}

?>
