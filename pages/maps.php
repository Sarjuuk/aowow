<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class MapsPage extends GenericPage
{
    protected $tpl           = 'maps';
    protected $tabId         = 1;
    protected $mode          = CACHETYPE_NONE;
    protected $js            = array(
        'maps.js',
        'Mapper.js'
    );
    protected $css           = array(
        ['string' => 'zone-picker { margin-left: 4px }'],
        ['path' => 'Mapper.css'],
        ['path' => 'Mapper_ie6.css', 'ieCond' => 'lte IE 6']
    );

    public function __construct()
    {
        parent::__construct();

        $this->name = Lang::$maps['maps'];
    }

    protected function generateContent()
    {
        // add conditional js
        $this->addJS('?data=zones&locale=' . User::$localeId . '&t=' . $_SESSION['dataKey']);
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() {}
}

?>
