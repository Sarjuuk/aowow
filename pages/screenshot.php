<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotPage extends GenericPage
{
    protected $tpl         = 'screenshot';
    protected $js          = ['Cropper.js'];
    protected $css         = [['path' => 'Cropper.css']];
    protected $reqAuth     = true;

    private   $tmpPath     = 'static/uploads/temp/';
    private   $pendingPath = 'static/uploads/screenshots/pending/';
    private   $destination = null;
    protected $destType    = 0;
    protected $destTypeId  = 0;

    public function __construct($pageCall, $pageParam)
    {
        parent::__construct($pageCall, $pageParam);

        $this->name    = Lang::main('ssEdit');
        // do not htmlEscape caption. It's applied as textnode
        $this->caption = !empty($_POST['screenshotcaption']) ? $_POST['screenshotcaption'] : '';

        // what are its other uses..? (finalize is custom)
        if ($pageParam == 'finalize')
        {
            if (!$this->handleFinalize())
                $this->error();
        }
        else if ($pageParam != 'add')
            $this->error();

        // get screenshot destination
        foreach ($_GET as $k => $v)
        {
            if ($v)                                         // target delivered as empty type.typeId key
                continue;

            $x = explode('_', $k);                          // . => _ as array key
            if (count($x) != 2)
                continue;

            // no such type
            if (empty(Util::$typeClasses[$x[0]]))
                continue;

            $t = Util::$typeClasses[$x[0]];
            $c = [['id', intVal($x[1])]];
            if ($x[0] == TYPE_WORLDEVENT)                   // ohforfsake..
                $c = array_merge($c, ['OR', ['holidayId', intVal($x[1])]]);

            $this->destination = new $t($c);

            // no such typeId
             if ($this->destination->error)
                continue;

            $this->destType   = intVal($x[0]);
            $this->destTypeId = intVal($x[1]);
        }
    }

    private function handleFinalize()
    {
        if (empty($_SESSION['ssUpload']))
            return false;

        // as its saved in session it should be valid
        $file = $_SESSION['ssUpload']['file'];

        // check tmp file
        $fullPath = $this->tmpPath.$file.'_original.jpg';
        if (!file_exists($fullPath))
            return false;

        // check post data
        if (empty($_POST) || empty($_POST['selection']))
            return false;

        $dims = explode(',', $_POST['selection']);
        if (count($dims) != 4)
            return false;

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
            'INSERT INTO ?_screenshots (type, typeId, uploader, date, width, height, caption) VALUES (?d, ?d, ?d, UNIX_TIMESTAMP(), ?d, ?d, ?)',
            $_SESSION['ssUpload']['type'], $_SESSION['ssUpload']['typeId'],
            User::$id,
            $w, $h,
            $this->caption
        );

        // write to file
        if (is_int($newId))         // 0 is valid, NULL or FALSE is not
            imagejpeg($destImg, $this->pendingPath.$newId.'.jpg', 100);

        unset($_SESSION['ssUpload']);
        header('Location: ?user='.User::$displayName.'#screenshots');
    }

    protected function generateContent()
    {
        $maxW    = 488;
        $maxH    = 325;
        $minCrop = CFG_SCREENSHOT_MIN_SIZE;

        if ($minCrop <= 0)
        {
            Util::addNote(U_GROUP_DEV | U_GROUP_ADMIN, 'ScreenshotPage::generateContent() - config error: dimensions for uploaded screenshots egual or less than zero. Value forced to 200');
            $minCrop = 200;
        }

        if (!$this->destType)
        {
            $this->error = Lang::main('ssErrors', 'noDest');
            return;
        }

        if (User::$banStatus & ACC_BAN_SCREENSHOT)
        {
            $this->error = Lang::main('ssErrors', 'notAllowed');
            return;
        }

        if ($_ = $this->validateScreenshot($isPNG))
        {
            $this->error = $_;
            return;
        }

        $im = $isPNG ? $this->loadFromPNG() : $this->loadFromJPG();
        if (!$im)
        {
            $this->error = Lang::main('ssErrors', 'load');
            return;
        }

        $name  = User::$displayName.'-'.$this->destType.'-'.$this->destTypeId.'-'.Util::createHash(16);
        $oSize = $rSize = [imagesx($im), imagesy($im)];
        $rel   = $oSize[0] / $oSize[1];

        // not sure if this is the best way
        $_SESSION['ssUpload'] = array(
            'file'   => $name,
            'type'   => $this->destType,
            'typeId' => $this->destTypeId
        );

        // check for oversize and refit to crop-screen
        if ($rel >= 1.5 && $oSize[0] > $maxW)
            $rSize = [$maxW, $maxW / $rel];
        else if ($rel < 1.5 && $oSize[1] > $maxH)
            $rSize = [$maxH * $rel, $maxH];

        $this->writeImage($im, $oSize, $name.'_original');  // use this image for work
        $this->writeImage($im, $rSize, $name);              // use this image to display

        // r: resized; o: original
        // r: x <= 488 && y <= 325  while x proportional to y
        // mincrop is optional and specifies the minimum resulting image size
        $this->cropper = [
            'url'     => $this->tmpPath.$name.'.jpg',
            'parent'  => 'ss-container',
            'oWidth'  => $oSize[0],
            'rWidth'  => $rSize[0],
            'oHeight' => $oSize[1],
            'rHeight' => $rSize[1],
        ];

        $infobox = [];

        // target
        $infobox[] = sprintf(Lang::main('displayOn'), Util::ucFirst(Lang::game(Util::$typeStrings[$this->destType])), Util::$typeStrings[$this->destType], $this->destTypeId);
        $this->extendGlobalIds($this->destType, $this->destTypeId);

        // dimensions
        $infobox[] = Lang::main('originalSize').Lang::main('colon').$oSize[0].' x '.$oSize[1];
        $infobox[] = Lang::main('targetSize').Lang::main('colon').'[span id=qf-newSize][/span]';

        // minimum dimensions
        if (!User::isInGroup(U_GROUP_STAFF))
        {
            $infobox[] = Lang::main('minSize').Lang::main('colon').$minCrop.' x '.$minCrop;
            $this->cropper['minCrop'] = $minCrop;
        }

        $this->infobox = '[ul][li]'.implode('[/li][li]', $infobox).'[/li][/ul]';
    }

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
            return Lang::main('ssErrors', 'noUpload');

        switch ($_FILES['screenshotfile']['error'])
        {
            case 1:
                return sprintf(Lang::main('ssErrors', 'maxSize'), ini_get('upload_max_filesize'));;
            case 3:
                return Lang::main('ssErrors', 'interrupted');
            case 4:
                return Lang::main('ssErrors', 'noFile');
            case 6:
                Util::addNote(U_GROUP_ADMIN, 'ScreenshotPage::validateScreenshot() - temporary upload directory is not set');
                return Lang::main('intError');
            case 7:
                Util::addNote(U_GROUP_ADMIN, 'ScreenshotPage::validateScreenshot() - could not write temporary file to disk');
                return Lang::main('genericError');
        }

        // points to invalid file (hack attempt)
        if (!is_uploaded_file($_FILES['screenshotfile']['tmp_name']))
        {
            Util::addNote(U_GROUP_ADMIN, 'ScreenshotPage::validateScreenshot() - uploaded file not in upload directory');
            return Lang::main('genericError');
        }

        // invalid file
        $is = getimagesize($_FILES['screenshotfile']['tmp_name']);
        if (!$is || empty($is['mime']))
            return Lang::main('ssErrors', 'notImage');

        // allow jpeg, png
        switch ($is['mime'])
        {
            case 'image/png':
                $isPNG = true;
            case 'image/jpg':
            case 'image/jpeg':
                break;
            default:
                return Lang::main('ssErrors', 'wrongFormat');
        }

        // size-missmatch: 4k UHD upper limit; 150px lower limit
        if ($is[0] < 150 || $is[1] < 150)
            return sprintf(Lang::main('ssErrors', 'tooSmall'), 150, 150);

        if ($is[0] > 3840 || $is[1] > 2160)
            return sprintf(Lang::main('ssErrors', 'tooLarge'), 150, 150);

        return null;
    }

    protected function generatePath() { }
    protected function generateTitle()
    {
        array_unshift($this->title, Lang::main('ssUpload'));
    }
}

?>
