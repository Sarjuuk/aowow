<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class FilterBaseResponse extends TextResponse
{
    private  array  $catg   = [];
    private  string $page   = '';
    private ?Filter $filter = null;

    public function __construct(string $rawParam)
    {
        if (!$rawParam)
            return;

        parent::__construct($rawParam);

        $catg = $page = null;
        if (strstr($rawParam, '='))
            [$page, $catg] = explode('=', $rawParam);
        else
            $page = $rawParam;

        if (!$page || preg_match('/[^a-z\-]/i', $page))
            return;

        $this->page = strtolower($page);

        if ($catg !== null)
        {
            // category is a string for profiler (region.realm) but not passed through here
            foreach (explode('.', $catg) as $c)
            {
                if (preg_match('/\D/', $c))
                    break;

                $this->catg[] = intval($c);
            }
        }

        $opts = ['parentCats' => $this->catg];

        // so usually the page call is just the DBTypes file string with a plural 's' .. but then there are currencies
        $fileStr = match ($this->page)
        {
            'currencies' => 'currency',
            default      => substr($this->page, 0, -1)
        };

        // yes, the whole _POST! .. should the input fields be exposed and static so they can be evaluated via BaseResponse::initRequestData() ?
        if (!$this->filter = Type::newFilter($fileStr, $_POST, $opts))
            trigger_error('Filter::__construct - tried to init filter from bogus GET data', E_USER_WARNING);
    }

    protected function generate() : void
    {
        // could not build filter from $this->page > go to front page
        if (!$this->filter)
        {
            $this->redirectTo = '.';
            return;
        }

        $url = '?'.$this->page;

        $this->filter->mergeCat($this->catg);

        if ($this->catg)
            $url .= '='.implode('.', $this->catg);

        if ($x = $this->filter?->buildGETParam())
            $url .= '&filter='.$x;

        if ($this->filter->error)
            $_SESSION['error']['fi'] = $this->filter::class;

        // do get request
        $this->redirectTo = $url;
    }
}

?>
