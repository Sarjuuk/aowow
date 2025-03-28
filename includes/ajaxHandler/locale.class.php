<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxLocale extends AjaxHandler
{
    protected $_get = array(
        'locale' => ['filter' => FILTER_CALLBACK, 'options' => 'WoWLocale::tryFrom']
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
        if ($this->_get['locale']?->validate())
        {
            User::$preferedLoc = $this->_get['locale'];
            User::save(true);
        }

        return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.';
    }
}

?>
