<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxEdit extends AjaxHandler
{
    protected $_get = array(
        'qqfile' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextLine'],
        'guide'  => ['filter' => FILTER_SANITIZE_NUMBER_INT                                ]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$params)
            return;

        if ($params[0] == 'image')
            $this->handler = 'handleUpload';
        else if ($params[0] == 'article')                   // has it's own editor page
            $this->handler = null;
    }

    /*
        success: bool
            id:   image enumerator
            type: 3 ? png : jpg
            name: old filename
        error: errString
    */
    protected function handleUpload() : string
    {
        if (!User::$id || $this->_get['guide'] != 1)
            return Util::toJSON(['success' => false, 'error' => '']);

        require_once('includes/libs/qqFileUploader.class.php');

        $targetPath = 'static/uploads/guide/images/';
        $tmpPath    = 'static/uploads/temp/';
        $tmpFile    = User::$displayName.'-'.Type::GUIDE.'-0-'.Util::createHash(16);

        $uploader = new qqFileUploader(['jpg', 'jpeg', 'png'], 10 * 1024 * 1024);
        $result   = $uploader->handleUpload($tmpPath, $tmpFile, true);

        if (isset($result['success']))
        {
            $finfo = new finfo(FILEINFO_MIME);
            $mime  = $finfo->file($tmpPath.$result['newFilename']);
            if (preg_match('/^image\/(png|jpe?g)/i', $mime, $m))
            {
                $i = 1;                                     // image index
                if ($files = scandir($targetPath, SCANDIR_SORT_DESCENDING))
                    if (rsort($files, SORT_NATURAL) && $files[0] != '.' && $files[0] != '..')
                        $i = explode('.', $files[0])[0] + 1;

                $targetFile = $i . ($m[1] == 'png' ? '.png' : '.jpg');

                // move to final location
                if (!rename($tmpPath.$result['newFilename'], $targetPath.$targetFile))
                    return Util::toJSON(['error' => Lang::main('intError')]);

                // send success
                return Util::toJSON(array(
                    'success' => true,
                    'id'      => $i,
                    'type'    => $m[1] == 'png' ? 3 : 2,
                    'name'    => $this->_get['qqfile']
                ));
            }

            return Util::toJSON(['error' => Lang::screenshot('error', 'unkFormat')]);
        }

        return Util::toJSON($result);
    }
}

?>
