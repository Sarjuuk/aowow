<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxLocale extends AjaxHandler
{
    protected $_get = array(
        'locale' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkLocale']
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        // always this one
        $this->handler    = 'handleLocale';
        $this->doRedirect = true;
    }

    /* responses
        header()
    */
    protected function handleLocale() : string
    {
        User::setLocale($this->_get['locale']);
        User::save();

        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.';
    }
}

?>
