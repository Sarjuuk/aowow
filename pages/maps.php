<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class MapsPage extends GenericPage
{
    protected $tpl           = 'maps';
    protected $tabId         = 1;
    protected $path          = [1, 1];
    protected $mode          = CACHE_TYPE_NONE;
    protected $js            = [[JS_FILE, 'maps.js']];
    protected $css           = [[CSS_STRING, 'zone-picker { margin-left: 4px }']];

    public function __construct($pageCall, $__)
    {
        parent::__construct($pageCall, $__);

        $this->name = Lang::maps('maps');
    }

    protected function generateContent()
    {
        // add conditional js
        $this->addScript([JS_FILE, '?data=zones&locale='.User::$localeId.'&t='.$_SESSION['dataKey']]);
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() {}
}

?>
