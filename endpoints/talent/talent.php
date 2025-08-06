<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TalentBaseResponse extends TemplateResponse
{
    protected string $template   = 'talent';
    // js stuffs
    protected array  $breadcrumb = [1, 0];
    protected ?int   $activeTab  = parent::TAB_TOOLS;
    protected string $pageName   = 'talent';
    protected array  $scripts    = array(
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/talent.css'],
        [SC_JS_FILE,  '?data=glyphs'],
        [SC_JS_FILE,  'js/talent.js']
    );

    public bool   $gDataKey   = true;
    public string $tcType     = 'tc';                       // TalentCalculator
    public string $chooseType;

    protected function generate() : void
    {
        $this->h1         = Lang::main('talentCalc');
        $this->chooseType = Lang::main('chooseClass');

        array_unshift($this->title, $this->h1);

        parent::generate();
    }
}

?>
