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
    protected $mode          = CACHETYPE_PAGE;
    protected $js            = array(
        'swfobject.js',
        'Summary.js'
    );

    public function __construct($__, $id)
    {
        parent::__construct();

        $this->typeId = intVal($id);

        $this->subject = new ItemsetList(array(['id', $this->typeId]));
        if ($this->subject->error)
            $this->notFound(Lang::$game['itemset']);

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
        array_unshift($this->title, $this->name, Util::ucFirst(Lang::$game['itemset']));
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
            $infobox[] = Lang::$main['unavailable'];

        // holiday
        if ($h = $this->subject->getField('holidayId'))
        {
            $infobox[] = Lang::$game['eventShort'].Lang::$main['colon'].'[event='.$h.']';
            $this->extendGlobalIds(TYPE_GAMEVENT, $h);
        }

        // itemLevel
        if ($min = $this->subject->getField('minLevel'))
        {
            $foo = Lang::$game['level'].Lang::$main['colon'].$min;
            $max = $this->subject->getField('maxLevel');

            if ($min < $max)
                $foo .= ' - '.$max;

            $infobox[] = $foo;
        }

        // class
        if ($cl = Lang::getClassString($this->subject->getField('classMask'), $jsg, $qty, false))
        {
            $this->extendGlobalIds(TYPE_CLASS, $jsg);
            $t = $qty == 1 ? Lang::$game['class'] : Lang::$game['classes'];
            $infobox[] = Util::ucFirst($t).Lang::$main['colon'].$cl;
        }

        // required level
        if ($lvl = $this->subject->getField('reqLevel'))
            $infobox[] = sprintf(Lang::$game['reqLevel'], $lvl);

        // type
        if ($_ty)
            $infobox[] = Lang::$game['type'].Lang::$main['colon'].Lang::$itemset['types'][$_ty];

        // tag
        if ($_ta)
            $infobox[] = Lang::$itemset['_tag'].Lang::$main['colon'].'[url=?itemsets&filter=ta='.$_ta.']'.Lang::$itemset['notes'][$_ta].'[/url]';

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

            $pieces[] = array(
                'id'      => $itemId,
                'name'    => $iList->getField('name', true),
                'quality' => $iList->getField('quality'),
                'icon'    => $iList->getField('iconString'),
                'json'    => $data[$itemId]
            );
        }

        // spells
        $foo    = [];
        $spells = [];
        for ($i = 1; $i < 9; $i++)
        {
            $spl = $this->subject->getField('spell'.$i);
            $qty = $this->subject->getField('bonus'.$i);

            if ($spl && $qty)
            {
                $foo[]    = $spl;
                $spells[] = array(                          // cant use spell as index, would change order
                    'id'    => $spl,
                    'bonus' => $qty,
                    'desc'  => ''
                );
            }
        }

        // sort by required pieces ASC
        usort($spells, function($a, $b) {
            if ($a['bonus'] == $b['bonus'])
                return 0;

            return ($a['bonus'] > $b['bonus']) ? 1 : -1;
        });

        $setSpells = new SpellList(array(['s.id', $foo]));
        foreach ($setSpells->iterate() as $spellId => $__)
        {
            foreach ($spells as &$s)
            {
                if ($spellId != $s['id'])
                    continue;

                $s['desc'] = $setSpells->parseText('description')[0];
            }
        }

        $skill = '';
        if ($_sk = $this->subject->getField('skillId'))
        {
            $spellLink = sprintf('<a href="?spells=11.%s">%s</a> (%s)', $_sk, Lang::$spell['cat'][11][$_sk][0], $this->subject->getField('skillLevel'));
            $skill = ' &ndash; <small><b>'.sprintf(Lang::$game['requires'], $spellLink).'</b></small>';
        }

        $this->bonusExt    = $skill;
        $this->description = $_ta ? sprintf(Lang::$itemset['_desc'], $this->name, Lang::$itemset['notes'][$_ta], $_cnt) : sprintf(Lang::$itemset['_descTagless'], $this->name, $_cnt);
        $this->unavailable = $this->subject->getField('cuFlags') & CUSTOM_UNAVAILABLE;
        $this->infobox     = $infobox ? '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]' : null;
        $this->pieces      = $pieces;
        $this->spells      = $spells;
        $this->expansion   = 0;
        $this->redButtons  = array(
            BUTTON_WOWHEAD => $this->typeId > 0,            // bool only
            BUTTON_LINKS   => ['color' => '', 'linkId' => ''],
            BUTTON_VIEW3D  => ['type' => TYPE_ITEMSET, 'typeId' => $this->typeId, 'equipList' => $eqList],
            BUTTON_COMPARE => ['eqList' => implode(':', $compare), 'qty' => $_cnt]
        );
        $this->compare     = array(
            'level' => $this->subject->getField('reqLevel'),
            'items' => array_map(function ($v) {
                           return [[$v]];
                       }, $compare)
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
        else if ($this->subject->getField('holidayId'))
        {
            $rel[] = ['id', $this->typeId, '!'];
            $rel[] = ['holidayId', 0, '!'];
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
                $lv = array(
                    'file'   => 'itemset',
                    'data'   => $relSets->getListviewData(),
                    'params' => array(
                        'id'   => 'see-also',
                        'name' => '$LANG.tab_seealso',
                        'tabs' => '$tabsRelated'
                    )
                );

                if (!$relSets->hasDiffFields(['classMask']))
                    $lv['params']['hiddenCols'] = "$['classes']";

                $this->lvData[] = $lv;

                $this->extendGlobalData($relSets->getJSGlobals());
            }
        }
    }
}




?>
