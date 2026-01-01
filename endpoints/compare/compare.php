<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class CompareBaseResponse extends TemplateResponse
{
    protected  string $template      = 'compare';
    protected  string $pageName      = 'compare';
    protected ?int    $activeTab     = parent::TAB_TOOLS;
    protected  array  $breadcrumb    = [1, 3];

    protected  array  $dataLoader    = ['weight-presets', 'gems', 'enchants', 'itemsets'];
    protected  array  $scripts       = array(
        [SC_JS_FILE,  'js/profile.js'],
        [SC_JS_FILE,  'js/Draggable.js'],
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/Summary.js'],
        [SC_CSS_FILE, 'css/Summary.css']
    );
    protected  array $expectedGET    = array(
        'compare'        => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkCompareString']]
    );
    protected  array $expectedCOOKIE = array(
        'compare_groups' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkCompareString']]
    );

    public Summary $summary;
    public array   $cmpItems = [];

    private string $compareString = '';

    public function __construct($rawParam)
    {
        parent::__construct($rawParam);

        // prefer GET over COOKIE
        if ($this->_get['compare'])
            $this->compareString = $this->_get['compare'];
        else if ($this->_cookie['compare_groups'])
            $this->compareString = $this->_cookie['compare_groups'];
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('compareTool');


        array_unshift($this->title, $this->h1);


        $this->summary = new Summary(array(
            'template' => 'compare',
            'id'       => 'compare',
            'parent'   => 'compare-generic'
        ));

        if ($this->compareString)
        {
            $items = [];
            foreach (explode(';', $this->compareString) as $itemsString)
            {
                $suGroup = [];
                foreach (explode(':', $itemsString) as $itemDef)
                {
                    // [itemId, subItem, permEnch, tempEnch, gem1, gem2, gem3, gem4]
                    $params    = array_pad(array_map('intVal', explode('.', $itemDef)), 8, 0);
                    $items[]   = $params[0];
                    $suGroup[] = $params;
                }

                $this->summary->addGroup($suGroup);
            }

            $iList = new ItemList(array(['i.id', $items]));
            $data  = $iList->getListviewData(ITEMINFO_SUBITEMS | ITEMINFO_JSON);

            foreach ($iList->iterate() as $itemId => $__)
            {
                if (empty($data[$itemId]))
                    continue;

                if (!empty($data[$itemId]['subitems']))
                    foreach ($data[$itemId]['subitems'] as &$si)
                    {
                        $si['enchantment'] = implode(', ', $si['enchantment']);
                        unset($si['chance']);
                    }

                $this->cmpItems[$itemId] = [
                    'name_'.Lang::getLocale()->json() => $iList->getField('name', true),
                    'quality'                         => $iList->getField('quality'),
                    'icon'                            => $iList->getField('iconString'),
                    'jsonequip'                       => $data[$itemId]
                ];
            }
        }

        parent::generate();
    }

    protected static function checkCompareString(string $val) : string
    {
        $val = urldecode($val);
        if (preg_match('/[^-?\d\.:;]/', $val))
            return '';

        return $val;
    }
}

?>
