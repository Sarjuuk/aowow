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

    private EnchantmentEntry $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new EnchantmentEntry($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('enchantment'), Lang::enchantment('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobal());

        $this->h1 = $this->subject->name;

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

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        // reqLevel
        if ($_ = $this->subject->requiredLevel)
            $infobox[] = sprintf(Lang::game('reqLevel'), $_);

        // reqskill
        if ($_ = $this->subject->skillLine)
        {
            $this->extendGlobalIds(Type::SKILL, $_);

            $foo = Lang::game('requires', ['&nbsp;[skill='.$_.']']);
            if ($_ = $this->subject->skillLevel)
                $foo .= Lang::main('parensFmt', ['', $_]);

            $infobox[] = $foo;
        }

        // id
        $infobox[] = Lang::enchantment('id') . $this->typeId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

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
        for ($i = 0; $i < 3; $i++)
        {
            $_ty  = $this->subject->type[$i];
            $_qty = $this->subject->amount[$i];
            $_obj = $this->subject->object[$i];
            $_tip = [];

            switch ($_ty)
            {
                case ENCHANTMENT_TYPE_COMBAT_SPELL:
                case ENCHANTMENT_TYPE_EQUIP_SPELL:
                case ENCHANTMENT_TYPE_USE_SPELL:
                    [$spellId, $trigger, $charges, $procChance] = $this->subject->spells[$i];
                    $spl  = $this->subject->getRelSpell($spellId);
                    $this->effects[$i] = array(
                        'name'  => $this->fmtStaffTip(Lang::item('trigger', $trigger), 'Type: '.$_ty),
                        'proc'  => $procChance,
                        'value' => $_qty ?: null,
                        'tip'   => [],
                        'icon'  => new IconElement(
                            Type::SPELL,
                            $spellId,
                            $spl?->name ?: Util::ucFirst(Lang::game('spell')).' #'.$spellId,
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
        if ($_ = $this->subject->conditionId)
            $this->activation = Game::getEnchantmentCondition($_);


        /**************/
        /* Extra Tabs */
        /**************/

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        // used by gem
        $gemList = new ItemContainer(array(['gemEnchantmentId', $this->typeId]));
        if (!$gemList->error)
        {
            $this->extendGlobalData($gemList->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $gemList->getListviewData(),
                'name' => '$LANG.tab_usedby + \' \' + LANG.gems',
                'id'   => 'used-by-gem',
            ), ItemEntry::$brickFile));
        }

        // used by socket bonus
        $socketsList = new ItemContainer(array(['socketBonus', $this->typeId]));
        if (!$socketsList->error)
        {
            $this->extendGlobalData($socketsList->getJSGlobals());
            $this->lvTabs->addListviewTab(new Listview(array(
                'data' => $socketsList->getListviewData(),
                'name' => '$LANG.tab_socketbonus',
                'id'   => 'used-by-socketbonus',
            ), ItemEntry::$brickFile));
        }

        // used by spell
        // used by useItem
        $cnd = array(
            DB::OR,
            [DB::AND, ['effect1Id', SpellEntry::EFFECTS_ENCHANTMENT], ['effect1MiscValue', $this->typeId]],
            [DB::AND, ['effect2Id', SpellEntry::EFFECTS_ENCHANTMENT], ['effect2MiscValue', $this->typeId]],
            [DB::AND, ['effect3Id', SpellEntry::EFFECTS_ENCHANTMENT], ['effect3MiscValue', $this->typeId]],
        );
        $spellList = new SpellContainer($cnd);
        if (!$spellList->error)
        {
            $spellData = $spellList->getListviewData();
            $this->extendGlobalData($spellList->getJSGlobals());

            $spellIds = $spellList->getFoundIDs();
            $conditions = array(
                DB::OR,
                [DB::AND, ['spellTrigger1', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId1', $spellIds]],
                [DB::AND, ['spellTrigger2', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId2', $spellIds]],
                [DB::AND, ['spellTrigger3', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId3', $spellIds]],
                [DB::AND, ['spellTrigger4', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId4', $spellIds]],
                [DB::AND, ['spellTrigger5', [SPELL_TRIGGER_USE, SPELL_TRIGGER_USE_NODELAY]], ['spellId5', $spellIds]]
            );

            $ubItems = new ItemContainer($conditions);
            if (!$ubItems->error)
            {
                $this->extendGlobalData($ubItems->getJSGlobals(GLOBALINFO_SELF));
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $ubItems->getListviewData(),
                    'name' => '$LANG.tab_usedby + \' \' + LANG.types[3][0]',
                    'id'   => 'used-by-item',
                ), ItemEntry::$brickFile));
            }

            // remove found spells if they are used by an item
            if (!$ubItems->error)
            {
                foreach ($spellList->iterate() as $sId => $spellEntry)
                {
                    // if Perm. Enchantment display both
                    for ($i = 0; $i < 3; $i++)
                        if ($spellEntry->effectId[$i] == SPELL_EFFECT_ENCHANT_ITEM)
                            continue 2;

                    foreach ($ubItems->iterate() as $itemEntry)
                    {
                        if (array_search($sId, $itemEntry->spellId) !== false)
                        {
                            unset($spellData[$sId]);
                            break;
                        }
                    }
                }
            }

            if ($spellData)
                $this->lvTabs->addListviewTab(new Listview(array(
                    'data' => $spellData,
                    'name' => '$LANG.tab_usedby + \' \' + LANG.types[6][0]',
                    'id'   => 'used-by-spell',
                ), SpellEntry::$brickFile));
        }

        // used by randomAttrItem
        $ire = DB::Aowow()->selectAssoc(
           'SELECT *, ABS(`id`) AS ARRAY_KEY FROM ::itemrandomenchant WHERE `enchantId1` = %i OR `enchantId2` = %i OR `enchantId3` = %i OR `enchantId4` = %i OR `enchantId5` = %i',
            $this->typeId, $this->typeId, $this->typeId, $this->typeId, $this->typeId
        );
        if ($ire)
        {
            if ($iet = DB::World()->selectAssoc('SELECT `entry` AS ARRAY_KEY, `ench`, `chance` FROM item_enchantment_template WHERE `ench` IN %in', array_keys($ire)))
            {
                $randIds = [];                                  // transform back to signed format
                foreach ($iet as $tplId => $data)
                    $randIds[$ire[$data['ench']]['id'] > 0 ? $tplId : -$tplId] = $ire[$data['ench']]['id'];

                $randItems = new ItemContainer(array(['randomEnchant', array_keys($randIds)]));
                if (!$randItems->error)
                {
                    $data = $randItems->getListviewData();
                    foreach ($randItems->iterate() as $iId => $itemEntry)
                    {
                        $re = $itemEntry->randomEnchant;

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
                    ), ItemEntry::$brickFile));
                }
            }
        }

        parent::generate();
    }

    private function getDistinctType() : int
    {
        $types = array_unique(array_filter($this->subject->type));
        if (count($types) == 1)
            return array_pop($types);
        return 0;
    }
}

?>
