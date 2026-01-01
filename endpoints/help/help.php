<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// these could also be defined as individual sub-pages ... haHA, no.
class HelpBaseResponse extends TemplateResponse
{
    protected  string $template   = 'text-page-generic';
    protected  string $pageName   = 'help';
    protected ?int    $activeTab  = parent::TAB_MORE;
    protected  array  $breadcrumb = [2, 13];

    protected  array  $validCats  = ['commenting-and-you', 'modelviewer', 'screenshots-tips-tricks', 'stat-weighting', 'talent-calculator', 'item-comparison', 'profiler', 'markup-guide'];

    private string $catg = '';

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if (!$rawParam)
            $this->generateError();

        $pageId = array_search($rawParam, $this->validCats);
        if ($pageId === false)
            $this->generateError();

        $this->catg = $rawParam;
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('moreTitles', $this->pageName, $this->catg);

        array_unshift($this->title, $this->h1);

        parent::generate();
    }
}

?>
