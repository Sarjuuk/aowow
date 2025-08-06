<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FilterBaseResponse extends TextResponse
{
    private  array  $catg   = [];
    private  string $page   = '';
    private ?Filter $filter = null;

    public function __construct(string $pageParam)
    {
        if (!$pageParam)
            return;

        parent::__construct($pageParam);

        $catg = null;
        if (strstr($pageParam, '='))
            [$this->page, $catg] = explode('=', $pageParam);
        else
            $this->page = $pageParam;

        if ($catg !== null)
            $this->catg = explode('.', $catg);

        $opts = ['parentCats' => $this->catg];

        // so usually the page call is just the DBTypes file string with a plural 's' .. but then there are currencies
        $fileStr = match ($this->page)
        {
            'currencies' => 'currency',
            default      => substr($this->page, 0, -1)
        };

        // yes, the whole _POST! .. should the input fields be exposed and static so they can be evaluated via BaseResponse::initRequestData() ?
        $this->filter = Type::newFilter($fileStr, $_POST, $opts);
    }

    protected function generate() : void
    {
        $url = '?'.$this->page;

        $this->filter?->mergeCat($this->catg);

        if ($this->catg)
            $url .= '='.implode('.', $this->catg);

        if ($x = $this->filter?->buildGETParam())
            $url .= '&filter='.$x;

        if ($this->filter?->error)
            $_SESSION['error']['fi'] = $this->filter::class;

        // do get request
        $this->redirectTo = $url;
    }
}

?>
