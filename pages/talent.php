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
    protected $scripts       = array(
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/talent.css']
    );

    protected $tcType        = 'tc';                        // tc: TalentCalculator; pc: PetCalculator
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
        if ($this->isPetCalc)
            $this->addScript(
                [SC_JS_FILE,  '?data=pet-talents.pets'],
                [SC_JS_FILE,  'js/petcalc.js'],
                [SC_JS_FILE,  'js/swfobject.js'],
                [SC_CSS_FILE, 'css/petcalc.css']
            );
        else
            $this->addScript(
                [SC_JS_FILE, '?data=glyphs'],
                [SC_JS_FILE, 'js/talent.js']
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
