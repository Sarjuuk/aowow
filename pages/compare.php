<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


// tabId 1: Tools g_initHeader()
class ComparePage extends GenericPage
{
    protected $tpl           = 'compare';
    protected $tabId         = 1;
    protected $path          = [1, 3];
    protected $mode          = CACHE_TYPE_NONE;
    protected $js            = array(
        'profile.js',
        'Draggable.js',
        'filters.js',
        'Summary.js',
        'swfobject.js',
    );
    protected $css           = [['path' => 'Summary.css']];

    protected $summary       = [];
    protected $cmpItems      = [];

    private   $compareString = '';

    public function __construct($pageCall, $__)
    {
        parent::__construct($pageCall, $__);

        // prefer $_GET over $_COOKIE
        if (!empty($_GET['compare']))
            $this->compareString = $_GET['compare'];
        else if (!empty($_COOKIE['compare_groups']))
            $this->compareString = urldecode($_COOKIE['compare_groups']);

        $this->name = Lang::main('compareTool');
    }

    protected function generateContent()
    {
        // add conditional js
        $this->addJS('?data=weight-presets.gems.enchants.itemsets&locale='.User::$localeId.'&t='.$_SESSION['dataKey']);

        $this->summary = array(
            'template' => 'compare',
            'id'       => 'compare',
            'parent'   => 'compare-generic'
        );

        if (!$this->compareString)
            return;

        $sets  = explode(';', $this->compareString);
        $items = $outSet = [];
        foreach ($sets as $set)
        {
            $itemSting = explode(':', $set);
            $outString = [];
            foreach ($itemSting as $substring)
            {
                $params  = explode('.', $substring);
                $items[] = (int)$params[0];
                while (sizeof($params) < 7)
                    $params[] = 0;

                $outString[] = $params;
            }

            $outSet[] = $outString;
        }

        $this->summary['groups'] = $outSet;

        $iList = new ItemList(array(['i.id', $items]));
        $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);

        foreach ($iList->iterate() as $itemId => $__)
        {
            if (empty($data[$itemId]))
                continue;

            if (!empty($data[$itemId]['subitems']))
                foreach ($data[$itemId]['subitems'] as &$si)
                    $si['enchantment'] = implode(', ', $si['enchantment']);

            $this->cmpItems[$itemId] = [
                'name_'.User::$localeString => $iList->getField('name', true),
                'quality'                   => $iList->getField('quality'),
                'icon'                      => $iList->getField('iconString'),
                'jsonequip'                 => $data[$itemId]
            ];
        }
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() {}
}

?>
