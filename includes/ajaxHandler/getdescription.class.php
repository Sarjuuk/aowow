<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxGetdescription extends AjaxHandler
{
    protected $_post = array(
        'description' => [FILTER_CALLBACK, ['options' => 'Aowow\AjaxHandler::checkTextBlob']]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$params || $params[0])                         // should be empty
            return;

        $this->handler = 'handleDescription';
    }

    protected function handleDescription() : string
    {
        $this->contentType = MIME_TYPE_TEXT;

        if (!User::canWriteGuide())
            return '';

        $desc = Markup::stripTags($this->_post['description']);

        return Lang::trimTextClean($desc, 120);
    }
}

?>
