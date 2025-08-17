<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GetdescriptionBaseResponse extends TextResponse
{
    protected string $contentType   = MIME_TYPE_TEXT;
    protected bool   $requiresLogin = true;

    protected array  $expectedPOST  = array(
        'description' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );

    public function __construct(string $param)
    {
        if ($param)                                         // should be empty
            $this->generate404();

        parent::__construct($param);
    }

    protected function generate() : void
    {
        if (!User::canWriteGuide())
            return;

        $this->result = GuideMgr::createDescription($this->_post['description']);
    }
}

?>
