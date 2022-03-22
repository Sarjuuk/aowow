<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// tabId 1: Tools g_initHeader()
class TalentPage extends GenericPage
{
    protected $tpl           = 'talent';
    protected $tabId         = 1;
    protected $path          = [1];
    protected $mode          = CACHE_TYPE_NONE;
    protected $gDataKey      = true;
    protected $js            = [[JS_FILE, 'TalentCalc.js']];
    protected $css           = array(
        [CSS_FILE, 'talentcalc.css'],
        [CSS_FILE, 'talent.css']
    );

    private   $isPetCalc     = false;

    public function __construct($pageCall, $__)
    {
        parent::__construct($pageCall, $__);

        $this->isPetCalc = $pageCall == 'petcalc';
        $this->name      = $this->isPetCalc ? Lang::main('petCalc') : Lang::main('talentCalc');
    }

    protected function generateContent()
    {
        // add conditional js & css
        $this->addScript(
            [JS_FILE, ($this->isPetCalc ? '?data=pet-talents.pets' : '?data=glyphs').'&locale='.User::$localeId.'&t='.$_SESSION['dataKey']],
            [JS_FILE,  $this->isPetCalc ? 'petcalc.js'             : 'talent.js']
        );

        if ($this->isPetCalc)
            $this->addScript(
                [JS_FILE,  'swfobject.js'],
                [CSS_FILE, 'petcalc.css']
            );

        $this->tcType  = $this->isPetCalc ? 'pc' : 'tc';
    }

    protected function generateTitle()
    {
        array_unshift($this->title, $this->name);
    }

    protected function generatePath()
    {
        $this->path[] = $this->isPetCalc ? 2 : 0;
    }
}

?>
