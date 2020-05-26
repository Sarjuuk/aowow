<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AjaxHandler
{
    protected $validParams = [];
    protected $params      = [];
    protected $handler;

    protected $contentType = MIME_TYPE_JSON;

    protected $_post       = [];
    protected $_get        = [];

    public    $doRedirect = false;

    public function __construct(array $params)
    {
        $this->params = $params;

        foreach ($this->_post as $k => &$v)
            $v = isset($_POST[$k]) ? filter_input(INPUT_POST, $k, $v[0], $v[1]) : null;

        foreach ($this->_get  as $k => &$v)
            $v = isset($_GET[$k])  ? filter_input(INPUT_GET,  $k, $v[0], $v[1]) : null;
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

    protected function checkEmptySet(string $val) : bool
    {
        return $val === '';                                 // parameter is expected to be empty
    }

    protected function checkLocale(string $val) : int
    {
        if (preg_match('/^'.implode('|', array_keys(array_filter(Util::$localeStrings))).'$/', $val))
            return intVal($val);

        return -1;
    }

    protected function checkInt(string $val) : int
    {
        if (preg_match('/^-?\d+$/', $val))
            return intVal($val);

        return 0;
    }

    protected function checkIdList(string $val) : array
    {
        if (preg_match('/^-?\d+(,-?\d+)*$/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    protected function checkIdListUnsigned(string $val) : array
    {
        if (preg_match('/\d+(,\d+)*/', $val))
            return array_map('intVal', explode(',', $val));

        return [];
    }

    protected function checkFulltext(string $val) : string
    {
        // trim non-printable chars
        return preg_replace('/[\p{C}]/ui', '', $val);
    }
}
?>
