<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxCookie extends AjaxHandler
{
    public function __construct(array $params)
    {
        // note that parent::__construct has to come after this
        if (!$params || !User::$id)
            return;

        $this->_get = array(
            $this->params[0] => [FILTER_SANITIZE_STRING, 0xC], // FILTER_FLAG_STRIP_LOW | *_HIGH
        );

        // NOW we know, what to expect and sanitize
        parent::__construct($params);

        // always this one
        $this->handler = 'handleCookie';
    }

    /* responses
        0: success
        $: silent error
    */
    protected function handleCookie()
    {
        if (User::$id && $this->params && $this->_get[$this->params[0]])
            if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $this->params[0], $this->_get[$this->params[0]]))
                return 0;

        return null;
    }
}