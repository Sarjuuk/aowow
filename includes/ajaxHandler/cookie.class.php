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
            $params[0] => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextLine'],
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
    protected function handleCookie() : string
    {
        if (User::$id && $this->params && $this->_get[$this->params[0]])
        {
            if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $this->params[0], $this->_get[$this->params[0]]))
                return '0';
            else
                trigger_error('AjaxCookie::handleCookie - write to db failed', E_USER_ERROR);
        }
        else
            trigger_error('AjaxCookie::handleCookie - malformed request received', E_USER_ERROR);

        return '';
    }
}

?>
