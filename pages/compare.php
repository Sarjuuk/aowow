<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class ComparePage extends GenericPage
{
    protected $tpl           = 'compare';
    protected $tabId         = 1;
    protected $path          = [1, 3];
    protected $mode          = CACHE_TYPE_NONE;
    protected $scripts       = array(
        [SC_JS_FILE,  'js/profile.js'],
        [SC_JS_FILE,  'js/Draggable.js'],
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/Summary.js'],
        [SC_JS_FILE,  'js/swfobject.js'],
        [SC_CSS_FILE, 'css/Summary.css']
    );

    protected $summary       = [];
    protected $cmpItems      = [];

    protected $_get          = ['compare'        => ['filter' => FILTER_CALLBACK, 'options' => 'ComparePage::checkCompareString']];
    protected $_cookie       = ['compare_groups' => ['filter' => FILTER_CALLBACK, 'options' => 'ComparePage::checkCompareString']];

    private   $compareString = '';

    public function __construct($pageCall, $__)
    {
        parent::__construct($pageCall, $__);

        // prefer GET over COOKIE
        if ($this->_get['compare'])
            $this->compareString = $this->_get['compare'];
        else if ($this->_cookie['compare_groups'])
            $this->compareString = $this->_cookie['compare_groups'];

        $this->name = Lang::main('compareTool');
    }

    protected function generateContent()
    {
        // add conditional js
        $this->addScript([SC_JS_FILE, '?data=weight-presets.gems.enchants.itemsets']);

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
            $itemString = explode(':', $set);
            $outString  = [];
            foreach ($itemString as $is)
            {
                $params  = array_pad(explode('.', $is), 7, 0);
                $items[] = (int)$params[0];

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

    protected static function checkCompareString(string $val) : string
    {
        $val = urldecode($val);
        if (preg_match('/[^\d\.:;]/', $val))
            return '';

        return $val;
    }
}

?>
