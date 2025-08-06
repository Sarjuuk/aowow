<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PetcalcBaseResponse extends TemplateResponse
{
    protected string $template   = 'talent';
    // js stuffs
    protected array  $breadcrumb = [1, 2];
    protected ?int   $activeTab  = parent::TAB_TOOLS;
    protected string $pageName   = 'petcalc';
    protected array  $scripts    = array(
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/talent.css'],
        [SC_JS_FILE,  '?data=pet-talents.pets'],
        [SC_JS_FILE,  'js/petcalc.js'],
        [SC_JS_FILE,  'js/swfobject.js'],
        [SC_CSS_FILE, 'css/petcalc.css']
    );

    public bool   $gDataKey   = true;
    public string $tcType     = 'pc';                       // PetCalculator
    public string $chooseType;

    protected function generate() : void
    {
        $this->h1         = Lang::main('petCalc');
        $this->chooseType = Lang::main('chooseFamily');

        array_unshift($this->title, $this->h1);

        parent::generate();
    }
}

?>
