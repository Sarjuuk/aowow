<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ItemsetBaseResponse extends TemplateResponse implements ICache
{
    use TrDetailPage, TrCache;

    protected  int    $cacheType  = CACHE_TYPE_DETAIL_PAGE;

    protected  string $template   = 'itemset';
    protected  string $pageName   = 'itemset';
    protected ?int    $activeTab  = parent::TAB_DATABASE;
    protected  array  $breadcrumb = [0, 2];

    protected  array  $scripts    = [[SC_JS_FILE, 'js/Summary.js']];

    public  int     $type        = Type::ITEMSET;
    public  int     $typeId      = 0;
    public  string  $bonusExt    = '';
    public  string  $description = '';
    public  bool    $unavailable = false;
    public  array   $pieces      = [];
    public  array   $spells      = [];
    public ?Summary $summary     = null;

    private ItemsetList $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new ItemsetList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('itemset'), Lang::itemset('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobals());

        $this->h1 = $this->subject->getField('name', true);

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_ta  = $this->subject->getField('contentGroup');
        $_ty  = $this->subject->getField('type');
        $_sk  = $this->subject->getField('skillId');
        $_evt = $this->subject->getField('eventId');
        $_cnt = count($this->subject->getField('pieces'));
        $_cl  = ChrClass::fromMask($this->subject->getField('classMask'));


        /*************/
        /* Menu Path */
        /*************/

        if (count($_cl) == 1)
            $this->breadcrumb[] = $_cl[0];


        /**************/
        /* Page Title */
        /**************/

        array_unshift($this->title, $this->h1, Util::ucWords(Lang::game('itemset')));


        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        if ($this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE)
            $infobox[] = Lang::main('unavailable');

        // worldevent
        if ($_evt)
        {
            $infobox[] = Lang::game('eventShort', ['[event='.$_evt.']']);
            $this->extendGlobalIds(Type::WORLDEVENT, $_evt);
        }

        // itemLevel
        if ($min = $this->subject->getField('minLevel'))
            $infobox[] = Lang::game('level').Lang::main('colon').Util::createNumRange($min, $this->subject->getField('maxLevel'), ' - ');

        // class
        $jsg = [];
        if ($cl = Lang::getClassString($this->subject->getField('classMask'), $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$cl;
        }

        // required level
        if ($lvl = $this->subject->getField('reqLevel'))
            $infobox[] = Lang::game('reqLevel', [$lvl]);

        // type
        if ($_ty)
            $infobox[] = Lang::game('type').Lang::itemset('types', $_ty);

        // tag
        if ($_ta)
            $infobox[] = Lang::itemset('_tag').'[url=?itemsets&filter=ta='.$_ta.']'.Lang::itemset('notes', $_ta).'[/url]';

        // id
        $infobox[] = Lang::itemset('id') . $this->subject->getField('refSetId');

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.$this->subject->getField('name_loc0').'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        // pieces + Summary
        $eqList  = [];
        $compare = [];

        if (!$this->subject->pieceToSet)
            $cnd = [0];
        else
            $cnd = ['i.id', array_keys($this->subject->pieceToSet)];

        $iList = new ItemList(array($cnd));
        $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);
        foreach ($iList->iterate() as $itemId => $__)
        {
            if (empty($data[$itemId]))
                continue;

            $slot = $iList->getField('slot');
            $disp = $iList->getField('displayId');
            if ($slot && $disp)
                $eqList[] = [$slot, $disp];

            $compare[] = $itemId;

            $this->pieces[$itemId] = array(
                array(
                    'name_'.Lang::getLocale()->json() => $iList->getField('name', true),
                    'quality'                         => $iList->getField('quality'),
                    'icon'                            => $iList->getField('iconString'),
                    'jsonequip'                       => $data[$itemId]
                ),
                new IconElement(Type::ITEM, $itemId, $iList->getField('name', true), quality: $iList->getField('quality'), size: IconElement::SIZE_SMALL, align: 'right', element: 'iconlist-icon')
            );
        }

        if ($compare)
            $this->summary = new Summary(array(
                'template' => 'itemset',
                'id'       => 'itemset',
                'parent'   => 'summary-generic',
                'groups'   => array_map(fn ($x) => [[$x]], $compare),
                'level'    => $this->subject->getField('reqLevel')
            ));

        // required skill
        if ($_sk)
        {
            $spellLink = sprintf('<a href="?spells=11.%s">%s</a> (%s)', $_sk, Lang::spell('cat', 11, $_sk, 0), $this->subject->getField('skillLevel'));
            $this->bonusExt = ' &ndash; <small><b>'.Lang::game('requires', [$spellLink]).'</b></small>';
        }

        $this->description = $_ta ? Lang::itemset('_desc', [$this->h1, Lang::itemset('notes', $_ta), $_cnt]) : Lang::itemset('_descTagless', [$this->h1, $_cnt]);
        $this->unavailable = !!($this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE);
        $this->spells      = $this->subject->getBonuses();
    //  $this->expansion   = $this->subject->getField('expansion'); NYI - todo: add col to table
        $this->redButtons  = array(
            BUTTON_WOWHEAD => $this->typeId > 0,            // bool only
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['type' => Type::ITEMSET, 'typeId' => $this->typeId, 'equipList' => $eqList],
            BUTTON_COMPARE => $compare ? ['eqList' => implode(':', $compare), 'qty' => $_cnt] : false
        );


        /**************/
        /* Extra Tabs */
        /**************/

        // related sets (priority: 1: similar tag + class; 2: has event; 3: no tag + similar type, 4: similar type + profession)
        $rel = [];

        if ($_ta && count($_cl) == 1)
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['classMask', 1 << ($_cl[0] - 1), '&'];
            $rel[] = ['contentGroup', (int)$_ta];
        }
        else if ($_evt)
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['eventId', 0, '!'];
        }
        else if ($_sk)
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['contentGroup', 0];
            $rel[] = ['skillId', 0, '!'];
            $rel[] = ['type', $_ty];
        }
        else if (!$_ta && $_ty)
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['contentGroup', 0];
            $rel[] = ['type', $_ty];
            $rel[] = ['skillId', 0];
        }

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"], 'tabsRelated', true);

        if ($rel)
        {
            $relSets = new ItemsetList($rel);
            if (!$relSets->error)
            {
                $tabData = array(
                    'data' => $relSets->getListviewData(),
                    'id'   => 'see-also',
                    'name' => '$LANG.tab_seealso'
                );

                if (!$relSets->hasDiffFields('classMask'))
                    $tabData['hiddenCols'] = ['classes'];

                $this->lvTabs->addListviewTab(new Listview($tabData, ItemsetList::$brickFile));

                $this->extendGlobalData($relSets->getJSGlobals());
            }
        }

        parent::generate();
    }
}




?>
