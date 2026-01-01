<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AboutusBaseResponse extends TemplateResponse
{
    protected  string $template   = 'text-page-generic';
    protected  string $pageName   = 'aboutus';
    protected ?int    $activeTab  = parent::TAB_MORE;
    protected  array  $breadcrumb = [2, 0];

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if ($rawParam)
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
