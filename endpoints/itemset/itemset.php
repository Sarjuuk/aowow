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
    public ?string  $expansion   = null;

    private Itemset $subject;

    public function __construct(string $id)
    {
        parent::__construct($id);

        $this->typeId     = intVal($id);
        $this->contribute = Type::getClassAttrib($this->type, 'contribute') ?? CONTRIBUTE_NONE;
    }

    protected function generate() : void
    {
        $this->subject = new Itemset($this->typeId);
        if ($this->subject->error)
            $this->generateNotFound(Lang::game('itemset'), Lang::itemset('notFound'));

        $this->extendGlobalData($this->subject->getJSGlobal());

        $this->h1 = $this->subject->name;

        $this->gPageInfo += array(
            'type'   => $this->type,
            'typeId' => $this->typeId,
            'name'   => $this->h1
        );

        $_ta  = $this->subject->contentGroup;
        $_ty  = $this->subject->type;
        $_sk  = $this->subject->skillId;
        $_evt = $this->subject->eventId;
        $_cnt = count(array_filter($this->subject->items));
        $_cl  = $this->subject->classes;


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

        $infobox = Lang::getInfoBoxForFlags($this->subject->cuFlags);

        if ($this->subject->cuFlags & CUSTOM_UNAVAILABLE)
            $infobox[] = Lang::main('unavailable');

        // worldevent
        if ($_evt)
        {
            $infobox[] = Lang::game('eventShort', ['[event='.$_evt.']']);
            $this->extendGlobalIds(Type::WORLDEVENT, $_evt);
        }

        // itemLevel
        if ($min = $this->subject->minLevel)
            $infobox[] = Lang::game('level').Lang::main('colon').Util::createNumRange($min, $this->subject->maxLevel);

        // side if any
        if ($_ = $this->subject->side)
            $infobox[] = Lang::main('side').'[span class=icon-'.($_ == SIDE_ALLIANCE ? 'alliance' : 'horde').']'.Lang::game('si', $_).'[/span]';

        // class
        $jsg = [];
        if ($cl = Lang::getClassString($this->subject->classMask, $jsg, Lang::FMT_MARKUP))
        {
            $this->extendGlobalIds(Type::CHR_CLASS, ...$jsg);
            $t = count($jsg) == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$cl;
        }

        // required level
        if ($min = $this->subject->minReqLevel)
            $infobox[] = Lang::game('reqLevel', [Util::createNumRange($min, $this->subject->maxReqLevel)]);

        // type
        if ($_ty)
            $infobox[] = Lang::game('type').Lang::itemset('types', $_ty);

        // tag
        if ($_ta)
            $infobox[] = Lang::itemset('_tag').'[url=?itemsets&filter=ta='.$_ta.']'.Lang::itemset('notes', $_ta).'[/url]';

        // id
        $infobox[] = Lang::itemset('id') . $this->subject->refSetId;

        // original name
        if (Lang::getLocale() != Locale::EN)
            $infobox[] = Util::ucFirst(Lang::lang(Locale::EN->value) . Lang::main('colon')) . '[copy button=false]'.($this->subject->name)(Locale::EN).'[/copy][/li]';

        if ($infobox)
            $this->infobox = new InfoboxMarkup($infobox, ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');


        /****************/
        /* Main Content */
        /****************/

        // pieces + Summary
        $eqList  = [];
        $compare = [];

        if ($this->subject->pieceToSet)
        {
            $pieces = new ItemContainer(array(['id', array_keys($this->subject->pieceToSet)]));
            $data   = $pieces->getListviewData(LISTVIEWINFO_SUBITEMS | LISTVIEWINFO_ITEMEXTRA);
            foreach ($pieces->iterate() as $itemId => $itemEntry)
            {
                if (empty($data[$itemId]))
                    continue;

                $slot = $itemEntry->slot;
                $disp = $itemEntry->displayId;
                if ($slot && $disp)
                    $eqList[] = [$slot, $disp];

                $compare[] = $itemId;

                $this->pieces[$itemId] = array(
                    array(
                        'name_'.Lang::getLocale()->json() => $itemEntry->name,
                        'quality'                         => $itemEntry->quality,
                        'icon'                            => $itemEntry->icon,
                        'jsonequip'                       => $data[$itemId]
                    ),
                    new IconElement(Type::ITEM, $itemId, $itemEntry->name, quality: $itemEntry->quality, size: IconElement::SIZE_SMALL, align: 'right', element: 'iconlist-icon')
                );
            }
        }

        if ($compare)
            $this->summary = new Summary(array(
                'template' => 'itemset',
                'id'       => 'itemset',
                'parent'   => 'summary-generic',
                'groups'   => array_map(fn ($x) => [[$x]], $compare),
                'level'    => $this->subject->maxReqLevel
            ));

        // required skill
        if ($_sk && ($skill = DB::Aowow()->selectRow('SELECT `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8` FROM ::skillline WHERE `id` = %i', $_sk)))
        {
            $spellLink = sprintf('<a href="?spells=11.%s">%s</a>', $_sk, Util::localizedString($skill, 'name', true));
            $this->bonusExt = ' &ndash; <small><b>'.Lang::game('requires', [Lang::main('parensFmt', [$spellLink, $this->subject->skillLevel])]).'</b></small>';
        }

        $this->description = $_ta ? Lang::itemset('_desc', [$this->h1, Lang::itemset('notes', $_ta), $_cnt]) : Lang::itemset('_descTagless', [$this->h1, $_cnt]);
        $this->unavailable = !!($this->subject->cuFlags & CUSTOM_UNAVAILABLE);
        $this->spells      = $this->subject->getBonuses();
        $this->expansion   = Util::$expansionString[$this->subject->expansion];
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
            $relSets = new ItemsetContainer($rel);
            if (!$relSets->error)
            {
                $tabData = array(
                    'data' => $relSets->getListviewData(),
                    'id'   => 'see-also',
                    'name' => '$LANG.tab_seealso'
                );

                if (!$relSets->hasDiffFields('classMask'))
                    $tabData['hiddenCols'] = ['classes'];

                $this->lvTabs->addListviewTab(new Listview($tabData, Itemset::$brickFile));

                $this->extendGlobalData($relSets->getJSGlobals());
            }
        }

        parent::generate();
    }
}

?>
