<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EnchantmentBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'enchantment';
    protected  string $pageName   = 'enchantment';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 101];

    public int    $type       = Type::ENCHANTMENT;
    public int    $typeId     = 0;
    public array  $effects    = [];
    public string $activation = '';

    private EnchantmentList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new EnchantmentList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('enchantment'), Lang::enchantment('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        /*************/
        /* Menu Path */
        /*************/

        if ($_ = $this->getDistinctType())
            $this->breadcrumb[] = $_;


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucFirst(Lang::game('enchantment')));


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

            $foo = Lang::game('requires', ['&nbsp;[skill='.$_.']']);
            if ($_ = $this->subject->getField('skillLevel'))
                $foo .= ' ('.$_.')';

            $infobox[] = $foo;
        }

        // id
        $infobox[] = Lang::enchantment('id') . $this->typeId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

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
            $_tip = [];

            switch ($_ty)
            {
                case ENCHANTMENT_TYPE_COMBAT_SPELL:
                case ENCHANTMENT_TYPE_EQUIP_SPELL:
                case ENCHANTMENT_TYPE_USE_SPELL:
                    [$spellId, $trigger, $charges, $procChance] = $this->subject->getField('spells')[$i];
                    $spl  = $this->subject->getRelSpell($spellId);
                    $this->effects[$i] = array(
                        'name'  => $this->fmtStaffTip(Lang::item('trigger', $trigger), 'Type: '.$_ty),
                        'proc'  => $procChance,
                        'value' => $_qty ?: null,
                        'tip'   => [],
                        'icon'  => new IconElement(
                            Type::SPELL,
                            $spellId,
                            !$spl ? Util::ucFirst(Lang::game('spell')).' #'.$spellId : Util::localizedString($spl, 'name'),
                            $charges,
                            link: !!$spl
                        )
                    );
                    break;
                case ENCHANTMENT_TYPE_STAT:
                    if ($idx = Stat::getIndexFrom(Stat::IDX_ITEM_MOD, $_obj))
                        if ($jsonStat = Stat::getJsonString($idx))
                            $_tip = [User::isInGroup(U_GROUP_STAFF) ? $_obj : null, $jsonStat];
                    // DO NOT BREAK!
                case ENCHANTMENT_TYPE_DAMAGE:
                case ENCHANTMENT_TYPE_TOTEM:
                case ENCHANTMENT_TYPE_PRISMATIC_SOCKET:
                case ENCHANTMENT_TYPE_RESISTANCE:
                    $this->effects[$i] = array(
                        'name'  => $this->fmtStaffTip(Lang::enchantment('types', $_ty), 'Type: '.$_ty),
                        'proc'  => null,
                        'value' => $_qty,
                        'tip'   => $_tip,
                        'icon'  => null
                    );
                    if ($_ty == ENCHANTMENT_TYPE_RESISTANCE)
                        $this->effects[$i]['name'] .= Lang::main('colon').'('.$this->fmtStaffTip(Lang::getMagicSchools(1 << $_obj), 'Object: '.$_obj).')';
            }
        }

        // activation conditions
        if ($_ = $this->subject->getField('conditionId'))
            $this->activation = Game::getEnchantmentCondition($_);


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // used by gem
        $gemList = new ItemList(array(['gemEnchantmentId', $this->typeId]));
        if (!$gemList->error)
        {
            $this->extendGlobalData($gemList->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $gemList->getListviewData(),
                'name' => '$LANG.tab_usedby + \' \' + LANG.gems',
                'id'   => 'used-by-gem',
            ), ItemList::$brickFile));
        }

        // used by socket bonus
        $socketsList = new ItemList(array(['socketBonus', $this->typeId]));
        if (!$socketsList->error)
        {
            $this->extendGlobalData($socketsList->getJsGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $socketsList->getListviewData(),
                'name' => '$LANG.tab_socketbonus',
                'id'   => 'used-by-socketbonus',
            ), ItemList::$brickFile));
        }

        // used by spell
        // used by useItem
        $cnd = array(
            'OR',
            ['AND', ['effect1Id', SpellList::EFFECTS_ENCHANTMENT], ['effect1MiscValue', $this->typeId]],
            ['AND', ['effect2Id', SpellList::EFFECTS_ENCHANTMENT], ['effect2MiscValue', $this->typeId]],
            ['AND', ['effect3Id', SpellList::EFFECTS_ENCHANTMENT], ['effect3MiscValue', $this->typeId]],
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
                $this->extendGlobalData($ubItems->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $ubItems->getListviewData(),
                    'name' => '$LANG.tab_usedby + \' \' + LANG.types[3][0]',
                    'id'   => 'used-by-item',
                ), ItemList::$brickFile));
            }

            // remove found spells if they are used by an item
            if (!$ubItems->error)
            {
                foreach ($spellList->iterate() as $sId => $__)
                {
                    // if Perm. Enchantment display both
                    for ($i = 1; $i < 4; $i++)
                        if ($spellList->getField('effect'.$i.'Id') == SPELL_EFFECT_ENCHANT_ITEM)
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

            if ($spellData)
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $spellData,
                    'name' => '$LANG.tab_usedby + \' \' + LANG.types[6][0]',
                    'id'   => 'used-by-spell',
                ), SpellList::$brickFile));
        }

        // used by randomAttrItem
        $ire = DB::Aowow()->select(
           'SELECT *, ABS(`id`) AS ARRAY_KEY FROM ?_itemrandomenchant WHERE `enchantId1` = ?d OR `enchantId2` = ?d OR `enchantId3` = ?d OR `enchantId4` = ?d OR `enchantId5` = ?d',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );
        if ($ire)
        {
            if ($iet = DB::World()->select('SELECT `entry` AS ARRAY_KEY, `ench`, `chance` FROM item_enchantment_template WHERE `ench` IN (?a)', array_keys($ire)))
            {
                $randIds = [];                                  // transform back to signed format
                foreach ($iet as $tplId => $data)
                    $randIds[$ire[$data['ench']]['id'] > 0 ? $tplId : -$tplId] = $ire[$data['ench']]['id'];

                $randItems = new ItemList(array(['randomEnchant', array_keys($randIds)]));
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

                    $this->extendGlobalData($randItems->getJSGlobals(GLOBALINFO_SELF));
                    $this->lvTabs->addListviewTab(new Listview(array(
                        'data'      => $data,
                        'id'        => 'used-by-rand',
                        'name'      => '$LANG.tab_usedby + \' \' + \''.Lang::item('_rndEnchants').'\'',
                        'extraCols' => ['$Listview.extraCols.percent']
                    ), ItemList::$brickFile));
                }
            }
        }

        parent::generate();
    }

    private function getDistinctType() : int
    {
        $type = 0;
        for ($i = 1; $i < 4; $i++)
        {
            if ($_ = $this->subject->getField('type'.$i))
            {
                if ($type && $type != $_)                   // already set
                    return 0;
                else
                    $type = $_;
            }
        }

        return $type;
    }
}

?>
