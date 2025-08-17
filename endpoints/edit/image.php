<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class EditImageResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'qqfile' => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextLine']      ],
        'guide'  => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 1]]
    );

    /*
        success: bool
            id:   image enumerator
            type: 3 ? png : jpg
            name: old filename
        error: errString
    */
    protected function generate() : void
    {
        if (!$this->assertGET('qqfile', 'guide'))
        {
            $this->result = Util::toJSON(['success' => false, 'error' => Lang::main('genericError')]);
            return;
        }

        if (!User::canWriteGuide())
        {
            $this->result = Util::toJSON(['success' => false, 'error' => Lang::main('genericError')]);
            return;
        }

        $this->result = GuideMgr::handleUpload();

        if (isset($this->result['success']))
            $this->result += ['name' => $this->_get['qqfile']];

        $this->result = Util::toJSON($this->result);
    }
}

?>
