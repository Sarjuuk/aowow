<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SearchboxBaseResponse extends TemplateResponse
{
    protected  string $template   = 'text-page-generic';
    protected  string $pageName   = 'searchbox';
    protected ?int    $activeTab  = parent::TAB_MORE;
    protected  array  $breadcrumb = [2, 16];

    public function __construct(string $pageParam)
    {
        parent::__construct($pageParam);

        if ($pageParam)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::main('moreTitles', $this->pageName);

        array_unshift($this->title, $this->h1);

        parent::generate();
    }
}

?>
