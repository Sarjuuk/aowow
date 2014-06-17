<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class TalentPage extends GenericPage
{
    protected $tpl           = 'talent';
    protected $tabId         = 1;
    protected $mode          = CACHETYPE_NONE;
    protected $js            = ['TalentCalc.js'];
    protected $css           = array(
        ['path' => 'TalentCalc.css'],
        ['path' => 'talent.css'],
        ['path' => 'TalentCalc_ie6.css',  'ieCond' => 'lte IE 6'],
        ['path' => 'TalentCalc_ie67.css', 'ieCond' => 'lte IE 7'],
    );

    private   $isPetCalc     = false;

    public function __construct($pageCall)
    {
        parent::__construct();

        $this->isPetCalc = $pageCall == 'petcalc';
        $this->name      = $this->isPetCalc ? Lang::$main['petCalc'] : Lang::$main['talentCalc'];
    }

    protected function generateContent()
    {
        // add conditional js & css
        $this->addJS(array(
           ($this->isPetCalc ? '?data=pet-talents.pets' : '?data=glyphs').'&locale='.User::$localeId.'&t='.$_SESSION['dataKey'],
            $this->isPetCalc ? 'petcalc.js'   : 'talent.js',
            $this->isPetCalc ? 'swfobject.js' : null
        ));
        $this->addCSS($this->isPetCalc ? ['path' => 'petcalc.css'] : null);

        $this->tcType  = $this->isPetCalc ? 'pc' : 'tc';
        $this->dataKey = $_SESSION['dataKey'];
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath() {}
}

?>
