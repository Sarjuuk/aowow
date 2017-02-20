<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// menuId 2: Itemset  g_initPath()
//  tabId 0: Database g_initHeader()
class ItemsetPage extends GenericPage
{
    use DetailPage;

    protected $type          = TYPE_ITEMSET;
    protected $typeId        = 0;
    protected $tpl           = 'itemset';
    protected $path          = [0, 2];
    protected $tabId         = 0;
    protected $mode          = CACHE_TYPE_PAGE;
    protected $js            = array(
        'swfobject.js',
        'Summary.js'
    );

    public function __construct($pageCall, $id)
    {
        parent::__construct($pageCall, $id);

        // temp locale
        if ($this->mode == CACHE_TYPE_TOOLTIP && isset($_GET['domain']))
            Util::powerUseLocale($_GET['domain']);

        $this->typeId = intVal($id);

        $this->subject = new ItemsetList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound();

        $this->name = $this->subject->getField('name', true);
        $this->extendGlobalData($this->subject->getJSGlobals());
    }

    protected function generatePath()
    {
        if ($_ = $this->subject->getField('classMask'))
        {
            $bit = log($_, 2);
            if (intVal($bit) != $bit)                       // bit is float => multiple classes were set => skip out
                return;

            $this->path[] = $bit + 1;
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::game('itemset')));
    }

    protected function generateContent()
    {
        $_ta  = $this->subject->getField('contentGroup');
        $_ty  = $this->subject->getField('type');
        $_cnt = count($this->subject->getField('pieces'));

        /***********/
        /* Infobox */
        /***********/

        $infobox = Lang::getInfoBoxForFlags($this->subject->getField('cuFlags'));

        // unavailable (todo (low): set data)
        if ($this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE)
            $infobox[] = Lang::main('unavailable');

        // worldevent
        if ($e = $this->subject->getField('eventId'))
        {
            $infobox[] = Lang::game('eventShort').Lang::main('colon').'[event='.$e.']';
            $this->extendGlobalIds(TYPE_WORLDEVENT, $e);
        }

        // itemLevel
        if ($min = $this->subject->getField('minLevel'))
        {
            $foo = Lang::game('level').Lang::main('colon').$min;
            $max = $this->subject->getField('maxLevel');

            if ($min < $max)
                $foo .= ' - '.$max;

            $infobox[] = $foo;
        }

        // class
        if ($cl = Lang::getClassString($this->subject->getField('classMask'), $jsg, $qty, false))
        {
            $this->extendGlobalIds(TYPE_CLASS, $jsg);
            $t = $qty == 1 ? Lang::game('class') : Lang::game('classes');
            $infobox[] = Util::ucFirst($t).Lang::main('colon').$cl;
        }

        // required level
        if ($lvl = $this->subject->getField('reqLevel'))
            $infobox[] = sprintf(Lang::game('reqLevel'), $lvl);

        // type
        if ($_ty)
            $infobox[] = Lang::game('type').Lang::main('colon').Lang::itemset('types', $_ty);

        // tag
        if ($_ta)
            $infobox[] = Lang::itemset('_tag').Lang::main('colon').'[url=?itemsets&filter=ta='.$_ta.']'.Lang::itemset('notes', $_ta).'[/url]';

        /****************/
        /* Main Content */
        /****************/

        // pieces + Summary
        $pieces  = [];
        $eqList  = [];
        $compare = [];

        if (!$this->subject->pieceToSet)
            $cnd = [0];
        else
            $cnd = ['i.id', array_keys($this->subject->pieceToSet)];

        $iList   = new ItemList(array($cnd));
        $data    = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);
        foreach ($iList->iterate() as $itemId => $__)
        {
            if (empty($data[$itemId]))
                continue;

            $slot = $iList->getField('slot');
            $disp = $iList->getField('displayId');
            if ($slot && $disp)
                $eqList[] = [$slot, $disp];

            $compare[] = $itemId;

            $pieces[$itemId] = array(
                'name_'.User::$localeString => $iList->getField('name', true),
                'quality'                   => $iList->getField('quality'),
                'icon'                      => $iList->getField('iconString'),
                'jsonequip'                 => $data[$itemId]
            );
        }

        $skill = '';
        if ($_sk = $this->subject->getField('skillId'))
        {
            $spellLink = sprintf('<a href="?spells=11.%s">%s</a> (%s)', $_sk, Lang::spell('cat', 11, $_sk, 0), $this->subject->getField('skillLevel'));
            $skill = ' &ndash; <small><b>'.sprintf(Lang::game('requires'), $spellLink).'</b></small>';
        }

        $this->bonusExt    = $skill;
        $this->description = $_ta ? sprintf(Lang::itemset('_desc'), $this->name, Lang::itemset('notes', $_ta), $_cnt) : sprintf(Lang::itemset('_descTagless'), $this->name, $_cnt);
        $this->unavailable = $this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE;
        $this->infobox     = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->pieces      = $pieces;
        $this->spells      = $this->subject->getBonuses();
        $this->expansion   = 0;
        $this->redButtons  = array(
            BUTTON_WOWHEAD => $this->typeId > 0,            // bool only
            BUTTON_LINKS   => ['type' => $this->type, 'typeId' => $this->typeId],
            BUTTON_VIEW3D  => ['type' => TYPE_ITEMSET, 'typeId' => $this->typeId, 'equipList' => $eqList],
            BUTTON_COMPARE => ['eqList' => implode(':', $compare), 'qty' => $_cnt]
        );
        $this->summary     = array(
            'id'       => 'itemset',
            'template' => 'itemset',
            'parent'   => 'summary-generic',
            'groups'   => array_map(function ($v) { return [[$v]]; }, $compare),
            'level'    => $this->subject->getField('reqLevel'),
        );

        /**************/
        /* Extra Tabs */
        /**************/

        // related sets (priority: 1: similar tag + class; 2: has event; 3: no tag + similar type, 4: similar type + profession)
        $rel = [];

        if ($_ta && count($this->path) == 3)
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['classMask', 1 << (end($this->path) - 1), '&'];
            $rel[] = ['contentGroup', (int)$_ta];
        }
        else if ($this->subject->getField('eventId'))
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['eventId', 0, '!'];
        }
        else if ($this->subject->getField('skillId'))
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

        if ($rel)
        {
            $relSets = new ItemsetList($rel);
            if (!$relSets->error)
            {
                $tabData = array(
                    'data' => array_values($relSets->getListviewData()),
                    'id'   => 'see-also',
                    'name' => '$LANG.tab_seealso'
                );

                if (!$relSets->hasDiffFields(['classMask']))
                    $tabData['hiddenCols'] = ['classes'];

                $this->lvTabs[] = ['itemset', $tabData];

                $this->extendGlobalData($relSets->getJSGlobals());
            }
        }
    }

    protected function generateTooltip($asError = false)
    {
        if ($asError)
            return '$WowheadPower.registerItemSet('.$this->typeId.', '.User::$localeId.', {});';

        $x = '$WowheadPower.registerItemSet('.$this->typeId.', '.User::$localeId.", {\n";
        $x .= "\tname_".User::$localeString.": '".Util::jsEscape($this->subject->getField('name', true))."',\n";
        $x .= "\ttooltip_".User::$localeString.": '".$this->subject->renderTooltip()."'\n";
        $x .= "});";

        return $x;
    }

    public function display($override = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::display($override);

        if (!$this->loadCache($tt))
        {
            $tt = $this->generateTooltip();
            $this->saveCache($tt);
        }

        header('Content-type: application/x-javascript; charset=utf-8');
        die($tt);
    }

    public function notFound($title = '', $msg = '')
    {
        if ($this->mode != CACHE_TYPE_TOOLTIP)
            return parent::notFound($title ?: Lang::game('itemset'), $msg ?: Lang::itemset('notFound'));

        header('Content-type: application/x-javascript; charset=utf-8');
        echo $this->generateTooltip(true);
        exit();
    }
}




?>
