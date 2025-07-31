<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AjaxFilter extends AjaxHandler
{
    public    $doRedirect = true;

    private   $cat        = [];
    private   $page       = '';
    private   $filter     = null;

    public function __construct(array $params)
    {
        if (!$params)
            return;

        parent::__construct($params);

        $p = explode('=', $params[0]);

        $this->page = $p[0];

        if (isset($p[1]))
            $this->cat[] = $p[1];

        if (count($params) > 1)
            for ($i = 1; $i < count($params); $i++)
                $this->cat[] = $params[$i];

        $opts = ['parentCats' => $this->cat];

        // so usually the page call is just the DBTypes file string with a plural 's' .. but then there are currencies
        $fileStr = match ($this->page)
        {
            'currencies' => 'currency',
            default      => substr($this->page, 0, -1)
        };

        // yes, the whole _POST! .. should the input fields be exposed and static so they can be evaluated via BaseResponse::initRequestData() ?
        $this->filter = Type::newFilter($fileStr, $_POST, $opts);

        // always this one
        $this->handler = 'handleFilter';
    }

    protected function handleFilter() : string
    {
        $url = '?'.$this->page;

        $this->filter?->mergeCat($this->cat);

        if ($this->cat)
            $url .= '='.implode('.', $this->cat);

        if ($x = $this->filter?->buildGETParam())
            $url .= '&filter='.$x;

        if ($this->filter?->error)
            $_SESSION['error']['fi'] = get_class($this->filter);

        // do get request
        return $url;
    }
}

?>
