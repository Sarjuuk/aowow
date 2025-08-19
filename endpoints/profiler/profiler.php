<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ProfilerBaseResponse extends TemplateResponse
{
    protected  string $template   = 'profiler';
    protected  string $pageName   = 'profiler';
    protected ?int    $activeTab  = parent::TAB_TOOLS;
    protected  array  $breadcrumb = [1, 5];

    protected  array  $dataLoader = ['realms'];
    protected  array  $scripts    = array(
        [SC_JS_FILE,  'js/profile_all.js'],
        [SC_JS_FILE,  'js/profile.js'],
        [SC_CSS_FILE, 'css/Profiler.css']
    );

    public bool   $gDataKey = true;
    public array  $regions  = [];
    public string $rg       = 'us';                         // preselected region in form

    public function __construct(string $params)
    {
        parent::__construct($params);

        if (!Cfg::get('PROFILER_ENABLE'))
            $this->generateError();
    }

    protected function generate() : void
    {
        //                                                              just so the form does not break. There won't be any results.
        $usedRegions = array_column(Profiler::getRealms(), 'region') ?: ['us'];
        foreach (Util::$regions as $idx => $id)
            if (in_array($id, $usedRegions))
                $this->regions[$id] = [Lang::profiler('regions', $id), $idx + 1];

        if (!in_array($this->rg, $usedRegions))
            $this->rg = key($this->regions);

        array_unshift($this->title, Util::ucFirst(Lang::profiler('profiler')));

        parent::generate();
    }
}

?>
