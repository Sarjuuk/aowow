<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxGotocomment extends AjaxHandler
{
    protected $_get = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt']
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        // always this one
        $this->handler    = 'handleGoToComment';
        $this->doRedirect = true;
    }

    /* responses
        header()
    */
    protected function handleGoToComment() : string
    {
        if (!$this->_get['id'])
            return '.';                                           // go home

        if ($_ = DB::Aowow()->selectRow('SELECT IFNULL(c2.id, c1.id) AS id, IFNULL(c2.type, c1.type) AS type, IFNULL(c2.typeId, c1.typeId) AS typeId FROM ?_comments c1 LEFT JOIN ?_comments c2 ON c1.replyTo = c2.id WHERE c1.id = ?d', $this->_get['id']))
            return '?'.Type::getFileString(intVal($_['type'])).'='.$_['typeId'].'#comments:id='.$_['id'].($_['id'] != $this->_get['id'] ? ':reply='.$this->_get['id'] : null);
        else
            trigger_error('AjaxGotocomment::handleGoToComment - could not find comment #'.$this->_get['id'], E_USER_ERROR);

        return '.';
    }
}

?>
