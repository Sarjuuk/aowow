<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
->  1. =add: receives user upload
    1.1. checks and processing on the upload
    1.2. forward to =crop or blank response
    2. =crop: user edites upload
    3. =complete: store edited screenshot file and data
    4. =thankyou
*/

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotAddResponse extends TextResponse
{
    protected bool $requiresLogin = true;

    private string $imgHash    = '';
    private int    $destType   = 0;
    private int    $destTypeId = 0;

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generate404();

        [, $this->destType, $this->destTypeId, , $imgHash] = $m;

        // no such type or this type cannot receive screenshots
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_SS))
            $this->generate404();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generate404();

        // only accept/expect hash for crop & complete
        if ($imgHash)
            $this->generate404();
    }

    protected function generate() : void
    {
        if ($this->handleAdd())
            $this->redirectTo = '?screenshot=crop&'.$this->destType.'.'.$this->destTypeId.'.'.$this->imgHash;
        else if ($this->destType && $this->destTypeId)
            $this->redirectTo = '?'.Type::getFileString($this->destType).'='.$this->destTypeId.'#submit-a-screenshot';
        else
            $this->generate404();
    }

    private function handleAdd() : bool
    {
        if (!User::canUploadScreenshot())
        {
            $_SESSION['error']['ss'] = Lang::screenshot('error', 'notAllowed');
            return false;
        }

        if (!ScreenshotMgr::init())
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        if (!ScreenshotMgr::validateUpload())
        {
            $_SESSION['error']['ss'] = ScreenshotMgr::$error;
            return false;
        }

        if (!ScreenshotMgr::loadUpload())
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        if (!ScreenshotMgr::tempSaveUpload([$this->destType, $this->destTypeId], $this->imgHash))
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        return true;
    }
}

?>
