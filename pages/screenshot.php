<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotPage extends GenericPage
{
    const     MAX_W        = 488;
    const     MAX_H        = 325;

    protected $infobox     = [];
    protected $cropper     = [];
    protected $extraHTML   = null;

    protected $tpl         = 'screenshot';
    protected $scripts     = [[SC_JS_FILE, 'js/Cropper.js'], [SC_CSS_FILE, 'css/Cropper.css']];
    protected $reqAuth     = true;
    protected $tabId       = 0;

    private   $tmpPath     = 'static/uploads/temp/';
    private   $pendingPath = 'static/uploads/screenshots/pending/';
    private   $destination = null;
    private   $minSize     = 200;
    private   $command     = '';

    protected $validCats   = ['add', 'crop', 'complete', 'thankyou'];
    protected $destType    = 0;
    protected $destTypeId  = 0;
    protected $imgHash     = '';

    protected $_post       = array(
        'coords'        => ['filter' => FILTER_CALLBACK, 'options' => 'ScreenshotPage::checkCoords'],
        'screenshotalt' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkTextBlob']
    );

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name    = Lang::screenshot('submission');
        $this->command = $pageParam;

        if ($ms = Cfg::get('SCREENSHOT_MIN_SIZE'))
            $this->minSize = abs($ms);
        else
            trigger_error('config error: Invalid value for minimum screenshot dimensions. Value forced to '.$this->minSize, E_USER_WARNING);

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m))
        {
            // no such type or this type cannot receive screenshots
            if (!Type::checkClassAttrib($m[1], 'contribute', CONTRIBUTE_SS))
                $this->error();

            $this->destination = Type::newList($m[1], [['id', intVal($m[2])]]);

            // no such typeId
            if (!$this->destination || $this->destination->error)
                $this->error();

            // only accept/expect hash for crop & complete
            if (empty($m[4]) && ($this->command == 'crop' || $this->command == 'complete'))
                $this->error();
            else if (!empty($m[4]) && ($this->command == 'add' || $this->command == 'thankyou'))
                $this->error();
            else if (!empty($m[4]))
                $this->imgHash = $m[4];

            $this->destType   = intVal($m[1]);
            $this->destTypeId = intVal($m[2]);
        }
        else
            $this->error();
    }

    protected function generateContent() : void
    {
        switch ($this->command)
        {
            case 'add':
                if ($this->handleAdd())
                    header('Location: ?screenshot=crop&'.$this->destType.'.'.$this->destTypeId.'.'.$this->imgHash, true, 302);
                else
                    header('Location: ?'.Type::getFileString($this->destType).'='.$this->destTypeId.'#submit-a-screenshot', true, 302);
                die();
            case 'crop':
                if (!$this->handleCrop())
                    header('Location: ?'.Type::getFileString($this->destType).'='.$this->destTypeId.'#submit-a-screenshot', true, 302);
                break;
            case 'complete':
                if ($_ = $this->handleComplete())
                    $this->notFound(Lang::main('nfPageTitle'), sprintf(Lang::main('intError2'), '#'.$_));
                else
                    header('Location: ?screenshot=thankyou&'.$this->destType.'.'.$this->destTypeId, true, 302);
                die();
            case 'thankyou':
                $this->tpl = 'list-page-generic';
                $this->handleThankyou();
                break;
        }
    }


    /*******************/
    /* command handler */
    /*******************/

    private function handleAdd() : bool
    {
        $this->imgHash = Util::createHash(16);

        if (!is_writable($this->tmpPath))
        {
            trigger_error('ScreenshotPage::handleAdd - temp upload directory not writable', E_USER_ERROR);
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        if (!User::canUploadScreenshot())
        {
            $_SESSION['error']['ss'] = Lang::screenshot('error', 'notAllowed');
            return false;
        }

        if ($_ = $this->validateScreenshot($isPNG))
        {
            $_SESSION['error']['ss'] = $_;
            return false;
        }

        $im = $isPNG ? $this->loadFromPNG() : $this->loadFromJPG();
        if (!$im)
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        $oSize = $rSize = [imagesx($im), imagesy($im)];
        $rel   = $oSize[0] / $oSize[1];

        // check for oversize and refit to crop-screen
        if ($rel >= 1.5 && $oSize[0] > self::MAX_W)
            $rSize = [self::MAX_W, self::MAX_W / $rel];
        else if ($rel < 1.5 && $oSize[1] > self::MAX_H)
            $rSize = [self::MAX_H * $rel, self::MAX_H];

        $success = true;

        // use this image for work
        if (!$this->writeImage($im, $oSize[0], $oSize[1], $this->ssName().'_original'))
            $success = false;

        // use this image to display
        if (!$this->writeImage($im, $rSize[0], $rSize[1], $this->ssName()))
            $success = false;

        return $success;
    }

    private function handleCrop() : bool
    {
        $tmpFile = $this->tmpPath.$this->ssName().'_original.jpg';

        if (!file_exists($tmpFile))
        {
            trigger_error('ScreenshotPage::handleCrop - temp image ('.$tmpFile.') not found', E_USER_ERROR);
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        $im = imagecreatefromjpeg($tmpFile);
        if (!$im)
        {
            trigger_error('ScreenshotPage::handleCrop - imagecreate failed ('.$tmpFile.')', E_USER_ERROR);
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        $oSize = $rSize = [imagesx($im), imagesy($im)];
        $rel   = $oSize[0] / $oSize[1];

        // check for oversize and refit to crop-screen
        if ($rel >= 1.5 && $oSize[0] > self::MAX_W)
            $rSize = [self::MAX_W, self::MAX_W / $rel];
        else if ($rel < 1.5 && $oSize[1] > self::MAX_H)
            $rSize = [self::MAX_H * $rel, self::MAX_H];

        // r: resized; o: original
        // r: x <= 488 && y <= 325  while x proportional to y
        // mincrop is optional and specifies the minimum resulting image size
        $this->cropper = [
            'url'     => Cfg::get('STATIC_URL').'/uploads/temp/'.$this->ssName().'.jpg',
            'parent'  => 'ss-container',
            'oWidth'  => $oSize[0],
            'rWidth'  => $rSize[0],
            'oHeight' => $oSize[1],
            'rHeight' => $rSize[1],
            'type'    => $this->destType,                   // only used to check against NPC: 15384 [OLDWorld Trigger (DO NOT DELETE)]
            'typeId'  => $this->destTypeId                  // i guess this was used to upload arbitrary imagery
        ];

        // minimum dimensions
        if (!User::isInGroup(U_GROUP_STAFF))
            $this->cropper['minCrop'] = $this->minSize;

        // target
        $this->infobox = sprintf(Lang::screenshot('displayOn'), Lang::typeName($this->destType), Type::getFileString($this->destType), $this->destTypeId);
        $this->extendGlobalIds($this->destType, $this->destTypeId);

        return true;
    }

    private function handleComplete() : int
    {
        // check tmp file
        $fullPath = $this->tmpPath.$this->ssName().'_original.jpg';
        if (!file_exists($fullPath))
            return 1;

        // check post data
        if (!$this->_post['coords'])
            return 2;

        $dims = $this->_post['coords'];
        if (count($dims) != 4)
            return 3;

        Util::checkNumeric($dims, NUM_CAST_FLOAT);

        // actually crop the image
        $srcImg = imagecreatefromjpeg($fullPath);

        $x = (int)(imagesx($srcImg) * $dims[0]);
        $y = (int)(imagesy($srcImg) * $dims[1]);
        $w = (int)(imagesx($srcImg) * $dims[2]);
        $h = (int)(imagesy($srcImg) * $dims[3]);

        $destImg = imagecreatetruecolor($w, $h);

        imagefill($destImg, 0, 0, imagecolorallocate($destImg, 255, 255, 255));
        imagecopy($destImg, $srcImg, 0, 0, $x, $y, $w, $h);
        imagedestroy($srcImg);

        // write to db
        $newId = DB::Aowow()->query(
            'INSERT INTO ?_screenshots (`type`, `typeId`, `userIdOwner`, `date`, `width`, `height`, `caption`, `status`) VALUES (?d, ?d, ?d, UNIX_TIMESTAMP(), ?d, ?d, ?, 0)',
            $this->destType, $this->destTypeId,
            User::$id,
            $w, $h,
            $this->_post['screenshotalt'] ?? ''
        );

        // write to file
        if (is_int($newId))         // 0 is valid, NULL or FALSE is not
            imagejpeg($destImg, $this->pendingPath.$newId.'.jpg', 100);
        else
            return 6;

        return 0;
    }

    private function handleThankyou() : void
    {
        $this->extraHTML  = Lang::screenshot('thanks', 'contrib').'<br><br>';
        $this->extraHTML .= sprintf(Lang::screenshot('thanks', 'goBack'), Type::getFileString($this->destType), $this->destTypeId)."<br /><br />\n";
        $this->extraHTML .= '<i>'.Lang::screenshot('thanks', 'note').'</i>';
    }


    /**********/
    /* helper */
    /**********/

    private function loadFromPNG() // : resource/gd
    {
        $image = imagecreatefrompng($_FILES['screenshotfile']['tmp_name']);
        $bg    = imagecreatetruecolor(imagesx($image), imagesy($image));

        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, true);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);

        return $bg;
    }

    private function loadFromJPG() // : resource/gd
    {
        return imagecreatefromjpeg($_FILES['screenshotfile']['tmp_name']);
    }

    private function writeImage(/*resource/gd*/ $im, int $w, int $h, string $file) : bool
    {
        if ($res = imagecreatetruecolor($w, $h))
        {
            if (imagecopyresampled($res, $im, 0, 0, 0, 0, $w, $h, imagesx($im), imagesy($im)))
            {
                if (imagejpeg($res, $this->tmpPath.$file.'.jpg', 100))
                    return true;
                else
                    trigger_error('ScreenshotPage::writeImage -  write failed', E_USER_ERROR);
            }
            else
                trigger_error('ScreenshotPage::writeImage - imagecopy failed', E_USER_ERROR);
        }
        else
            trigger_error('ScreenshotPage::writeImage - imagecreate failed', E_USER_ERROR);

        return false;
    }

    private function validateScreenshot(?bool &$isPNG = false) : string
    {
        // no upload happened or some error occured
        if (!$_FILES || empty($_FILES['screenshotfile']))
            return Lang::screenshot('error', 'selectSS');

        switch ($_FILES['screenshotfile']['error'])         // 0 is fine
        {
            case UPLOAD_ERR_INI_SIZE:                       // 1
            case UPLOAD_ERR_FORM_SIZE:                      // 2
                trigger_error('validateScreenshot - the file exceeds the maximum size of '.ini_get('upload_max_filesize'), E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case UPLOAD_ERR_PARTIAL:                        // 3
                trigger_error('validateScreenshot - upload was interrupted', E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case UPLOAD_ERR_NO_FILE:                        // 4
                trigger_error('validateScreenshot - no file was received', E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case UPLOAD_ERR_NO_TMP_DIR:                     // 6
                trigger_error('validateScreenshot - temporary upload directory is not set', E_USER_ERROR);
                return Lang::main('intError');
            case UPLOAD_ERR_CANT_WRITE:                     // 7
                trigger_error('validateScreenshot - could not write temporary file to disk', E_USER_ERROR);
                return Lang::main('intError');
            case UPLOAD_ERR_EXTENSION:                      // 8
                trigger_error('validateScreenshot - a php extension stopped the file upload.', E_USER_ERROR);
                return Lang::main('intError');
        }

        // points to invalid file (hack attempt)
        if (!is_uploaded_file($_FILES['screenshotfile']['tmp_name']))
        {
            trigger_error('validateScreenshot - uploaded file not in upload directory', E_USER_ERROR);
            return Lang::main('intError');
        }

        // check if file is an image; allow jpeg, png
        $finfo = new finfo(FILEINFO_MIME);                  // fileInfo appends charset information and other nonsense
        $mime  = $finfo->file($_FILES['screenshotfile']['tmp_name']);
        if (preg_match('/^image\/(png|jpe?g)/i', $mime, $m))
            $isPNG = $m[0] == 'image/png';
        else
            return Lang::screenshot('error', 'unkFormat');

        // invalid file
        $is = getimagesize($_FILES['screenshotfile']['tmp_name']);
        if (!$is)
            return Lang::screenshot('error', 'selectSS');

        // size-missmatch: 4k UHD upper limit; 150px lower limit
        if ($is[0] < $this->minSize || $is[1] < $this->minSize)
            return Lang::screenshot('error', 'tooSmall');
        else if ($is[0] > 3840 || $is[1] > 2160)
            return Lang::screenshot('error', 'selectSS');

        return '';
    }

    private function ssName() : string
    {
        return $this->imgHash ? User::$displayName.'-'.$this->destType.'-'.$this->destTypeId.'-'.$this->imgHash : '';
    }

    protected static function checkCoords(string $val) : array
    {
        if (preg_match('/^[01]\.[0-9]{3}(,[01]\.[0-9]{3}){3}$/', $val))
            return explode(',', $val);

        return [];
    }

    protected function generatePath() : void { }
    protected function generateTitle() : void
    {
        array_unshift($this->title, Lang::screenshot('submission'));
    }
}

?>
