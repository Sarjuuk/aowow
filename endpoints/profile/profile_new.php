<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfileNewResponse extends TemplateResponse
{
    use TrProfilerDetail;

    protected  string $template   = 'profile';
    protected  string $pageName   = 'profile&new';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 5, 1];              // Tools > Profiler > New

    protected  array  $dataLoader = ['enchants', 'gems', 'glyphs', 'itemsets', 'realms', 'statistics', 'weight-presets'];
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/filters.js'],
        [SC_JS_FILE,  'js/TalentCalc.js'],
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_JS_FILE,  'js/Profiler.js'],
        [SC_CSS_FILE, 'css/talentcalc.css'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    public int  $type     = Type::PROFILE;
    public bool $gDataKey = true;

    public function __construct(string $idOrProfile)
    {
        parent::__construct($idOrProfile);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();

        // why is id set > error
        if ($idOrProfile)
            $this->generateError();
    }

    protected function generate() : void
    {
        array_unshift($this->title, Util::ucFirst(Lang::game('profile')));

        parent::generate();
    }
}

?>
