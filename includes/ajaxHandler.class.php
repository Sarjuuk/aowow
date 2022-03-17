<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AjaxHandler
{
    use TrRequestData;

    protected $validParams = [];
    protected $params      = [];
    protected $handler;

    protected $contentType = MIME_TYPE_JSON;

    public    $doRedirect = false;

    public function __construct(array $params)
    {
        $this->params = $params;

        $this->initRequestData();
    }

    public function handle(string &$out) : bool
    {
        if (!$this->handler)
            return false;

        if ($this->validParams)
        {
            if (count($this->params) != 1)
                return false;

            if (!in_array($this->params[0], $this->validParams))
                return false;
        }

        $h   = $this->handler;
        $out = $this->$h();
        if ($out === null)
            $out = '';

        return true;
    }

    public function getContentType() : string
    {
        return $this->contentType;
    }

    protected function reqPOST(string ...$keys) : bool
    {
        foreach ($keys as $k)
            if (!isset($this->_post[$k]) || $this->_post[$k] === null || $this->_post[$k] === '')
                return false;

        return true;
    }

    protected function reqGET(string ...$keys) : bool
    {
        foreach ($keys as $k)
            if (!isset($this->_get[$k]) || $this->_get[$k] === null || $this->_get[$k] === '')
                return false;

        return true;
    }
}
?>
