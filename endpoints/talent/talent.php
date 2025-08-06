<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TalentBaseResponse extends TemplateResponse
{
    protected  string $template   = 'talent';
    protected  string $pageName   = 'talent';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 0];

    protected  array  $dataLoader = ['glyphs'];
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/talent.css'],
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
