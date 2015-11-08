<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotPage extends GenericPage
{
    const     MAX_W        = 488;
    const     MAX_H        = 325;

    protected $tpl         = 'screenshot';
    protected $js          = ['Cropper.js'];
    protected $css         = [['path' => 'Cropper.css']];
    protected $reqAuth     = true;
    protected $tabId       = 0;

    private   $tmpPath     = 'static/uploads/temp/';
    private   $pendingPath = 'static/uploads/screenshots/pending/';
    private   $destination = null;
    private   $minSize     = CFG_SCREENSHOT_MIN_SIZE;

    protected $validCats   = ['add', 'crop', 'complete', 'thankyou'];
    protected $destType    = 0;
    protected $destTypeId  = 0;
    protected $imgHash     = '';

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name    = Lang::screenshot('submission');
        $this->command = $pageParam;

        if ($this->minSize <= 0)
        {
            trigger_error('config error: dimensions for uploaded screenshots equal or less than zero. Value forced to 200', E_USER_WARNING);
            $this->minSize = 200;
        }

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'], $m))
        {
            // no such type
            if (empty(Util::$typeClasses[$m[1]]))
                $this->error();

            $t = Util::$typeClasses[$m[1]];
            $c = [['id', intVal($m[2])]];

            $this->destination = new $t($c);

            // no such typeId
            if ($this->destination->error)
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

    protected function generateContent()
    {
        switch ($this->command)
        {
            case 'add':
                if ($this->handleAdd())
                    header('Location: ?screenshot=crop&'.$this->destType.'.'.$this->destTypeId.'.'.$this->imgHash, true, 302);
                else
                    header('Location: ?'.Util::$typeStrings[$this->destType].'='.$this->destTypeId.'#submit-a-screenshot', true, 302);
                die();
            case 'crop':
                $this->handleCrop();
                break;
            case 'complete':
                if ($_ = $this->handleComplete())
                    $this->notFound(Lang::main('nfPageTitle'), sprintf(Lang::main('intError2'), '#'.$_));
                else
                    header('Location: ?screenshot=thankyou&'.$this->destType.'.'.$this->destTypeId, true, 302);
                die();
            case 'thankyou':
                $this->tpl = 'text-page-generic';
                $this->handleThankyou();
                break;
        }
    }


    /*******************/
    /* command handler */
    /*******************/


    private function handleAdd()
    {
        $this->imgHash = Util::createHash(16);

        if (User::$banStatus & ACC_BAN_SCREENSHOT)
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

        $name  = User::$displayName.'-'.$this->destType.'-'.$this->destTypeId.'-'.$this->imgHash;

        $this->writeImage($im, $oSize, $name.'_original');  // use this image for work
        $this->writeImage($im, $rSize, $name);              // use this image to display

        return true;
    }

    private function handleCrop()
    {
        $im = imagecreatefromjpeg($this->tmpPath.$this->ssName().'_original.jpg');

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
            'url'     => STATIC_URL.'/uploads/temp/'.$this->ssName().'.jpg',
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
        $this->infobox = sprintf(Lang::screenshot('displayOn'), Util::ucFirst(Lang::game(Util::$typeStrings[$this->destType])), Util::$typeStrings[$this->destType], $this->destTypeId);
        $this->extendGlobalIds($this->destType, $this->destTypeId);
    }

    private function handleComplete()
    {
        // check tmp file
        $fullPath = $this->tmpPath.$this->ssName().'_original.jpg';
        if (!file_exists($fullPath))
            return 1;

        // check post data
        if (empty($_POST) || empty($_POST['coords']))
            return 2;

        $dims = explode(',', $_POST['coords']);
        if (count($dims) != 4)
            return 3;

        Util::checkNumeric($dims);

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
            'INSERT INTO ?_screenshots (type, typeId, userIdOwner, date, width, height, caption) VALUES (?d, ?d, ?d, UNIX_TIMESTAMP(), ?d, ?d, ?)',
            $this->destType, $this->destTypeId,
            User::$id,
            $w, $h,
            !empty($_POST['screenshotalt']) ? $_POST['screenshotalt'] : ''
        );

        // write to file
        if (is_int($newId))         // 0 is valid, NULL or FALSE is not
            imagejpeg($destImg, $this->pendingPath.$newId.'.jpg', 100);
        else
            return 6;
    }

    private function handleThankyou()
    {
        $this->extraHTML  = Lang::screenshot('thanks', 'contrib').'<br><br>';
        $this->extraHTML .= sprintf(Lang::screenshot('thanks', 'goBack'), Util::$typeStrings[$this->destType], $this->destTypeId)."<br /><br />\n";
        $this->extraHTML .= '<i>'.Lang::screenshot('thanks', 'note').'</i>';
    }


    /**********/
    /* helper */
    /**********/


    private function loadFromPNG()
    {
        $image = imagecreatefrompng($_FILES['screenshotfile']['tmp_name']);
        $bg    = imagecreatetruecolor(imagesx($image), imagesy($image));

        imagefill($bg, 0, 0, imagecolorallocate($bg, 255, 255, 255));
        imagealphablending($bg, true);
        imagecopy($bg, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);

        return $bg;
    }

    private function loadFromJPG()
    {
        return imagecreatefromjpeg($_FILES['screenshotfile']['tmp_name']);
    }

    private function writeImage($im, $dims, $file)
    {
        if ($res = imagecreatetruecolor($dims[0], $dims[1]))
            if (imagecopyresampled($res, $im, 0, 0, 0, 0, $dims[0], $dims[1], imagesx($im), imagesy($im)))
                if (imagejpeg($res, $this->tmpPath.$file.'.jpg', 100))
                    return true;

        return false;
    }

    private function validateScreenshot(&$isPNG = false)
    {
        // no upload happened or some error occured
        if (!$_FILES || empty($_FILES['screenshotfile']))
            return Lang::screenshot('error', 'selectSS');

        switch ($_FILES['screenshotfile']['error'])
        {
            case 1:
                trigger_error('validateScreenshot - the file exceeds the maximum size of '.ini_get('upload_max_filesize'), E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case 3:
                trigger_error('validateScreenshot - upload was interrupted', E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case 4:
                trigger_error('validateScreenshot() - no file was received', E_USER_WARNING);
                return Lang::screenshot('error', 'selectSS');
            case 6:
                trigger_error('validateScreenshot - temporary upload directory is not set', E_USER_WARNING);
                return Lang::main('intError');
            case 7:
                trigger_error('validateScreenshot - could not write temporary file to disk', E_USER_WARNING);
                return Lang::main('intError');
        }

        // points to invalid file (hack attempt)
        if (!is_uploaded_file($_FILES['screenshotfile']['tmp_name']))
        {
            trigger_error('validateScreenshot - uploaded file not in upload directory', E_USER_WARNING);
            return Lang::main('intError');
        }

        // invalid file
        $is = getimagesize($_FILES['screenshotfile']['tmp_name']);
        if (!$is || empty($is['mime']))
            return Lang::screenshot('error', 'selectSS');

        // allow jpeg, png
        switch ($is['mime'])
        {
            case 'image/png':
                $isPNG = true;
            case 'image/jpg':
            case 'image/jpeg':
                break;
            default:
                return Lang::screenshot('error', 'unkFormat');
        }

        // size-missmatch: 4k UHD upper limit; 150px lower limit
        if ($is[0] < $this->minSize || $is[1] < $this->minSize)
            return Lang::screenshot('error', 'tooSmall');
        else if ($is[0] > 3840 || $is[1] > 2160)
            return Lang::screenshot('error', 'selectSS');

        return null;
    }

    private function ssName()
    {
        return $this->imgHash ? User::$displayName.'-'.$this->destType.'-'.$this->destTypeId.'-'.$this->imgHash : '';
    }

    protected function generatePath() { }
    protected function generateTitle()
    {
        array_unshift($this->title, Lang::screenshot('submission'));
    }
}

?>
