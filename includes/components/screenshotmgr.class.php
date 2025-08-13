<?php

namespace Aowow;

use GdImage;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ScreenshotMgr
{
    // config value
    public static int $MIN_SIZE;                            // 200
    // 4k resolution
    private const MAX_W = 4096;
    private const MAX_H = 2160;
    // scale img down if larger than
    private const LIMIT_W = 488;
    private const LIMIT_H = 325;

    // as expected by js - this also makes the CC-flags functionally exclusive tith each other
    private const STATUS_PENDING  = 0;
    private const STATUS_DELETED  = 999;
    private const STATUS_APPROVED = 100;
    private const STATUS_STICKY   = 105;

    public const MIME_UNK  = 0;
    public const MIME_JPG  = 1;
    public const MIME_PNG  = 2;                             // support more types? The wow client only uses jpg though, so even png is a stretch.

    private const DIMS_RESIZED = [772, 618];
    private const DIMS_THUMB   = [150, 150];

    public const PATH_TEMP    = 'static/uploads/temp/%s.jpg';
    public const PATH_PENDING = 'static/uploads/screenshots/pending/%d.jpg';
    public const PATH_THUMB   = 'static/uploads/screenshots/thumb/%d.jpg';
    public const PATH_RESIZED = 'static/uploads/screenshots/resized/%d.jpg';
    public const PATH_NORMAL  = 'static/uploads/screenshots/normal/%d.jpg';

    // upload handling
    private static  int     $mimeType = self::MIME_UNK;
    private static ?GdImage $img      = null;
    private static  string  $fileName = '';

    public static bool   $hasUpload = false;
    public static string $error     = '';

    public static function init() : bool
    {
        if ($ms = Cfg::get('SCREENSHOT_MIN_SIZE'))
            self::$MIN_SIZE = abs($ms);
        else
        {
            self::$MIN_SIZE = 200;
            trigger_error('ScreenshotMgr::init - config error: Invalid value for minimum screenshot dimensions. Value defaulted to 200', E_USER_WARNING);
        }

        $dirErr = false;
        foreach (['TEMP', 'PENDING', 'THUMB', 'RESIZED', 'NORMAL'] as $p)
        {
            $path = constant('self::PATH_' . $p);
            if (!is_writable(substr($path, 0, strrpos($path, '/'))))
            {
                trigger_error('ScreenshotMgr::init - directory '.substr($path, 0, strrpos($path, '/')).' not writable', E_USER_ERROR);
                $dirErr = true;
            }
        }

        if ($dirErr)
            return false;

        // active screenshot upload
        self::$hasUpload = $_FILES && !empty($_FILES['screenshotfile']);

        return true;
    }

    public static function validateUpload() : bool
    {
        if (!self::$hasUpload)
            return false;

        switch ($_FILES['screenshotfile']['error'])         // 0 is fine
        {
            case UPLOAD_ERR_INI_SIZE:                       // 1
            case UPLOAD_ERR_FORM_SIZE:                      // 2
                trigger_error('ScreenshotMgr::validateUpload - the file exceeds the maximum size of '.ini_get('upload_max_filesize'), E_USER_WARNING);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_PARTIAL:                        // 3
                trigger_error('ScreenshotMgr::validateUpload - upload was interrupted', E_USER_WARNING);
                self::$error = Lang::screenshot('error', 'selectSS');
                return false;
            case UPLOAD_ERR_NO_FILE:                        // 4
                trigger_error('ScreenshotMgr::validateUpload - no file was received', E_USER_WARNING);
                self::$error = Lang::screenshot('error', 'selectSS');
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:                     // 6
                trigger_error('ScreenshotMgr::validateUpload - temporary upload directory is not set', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_CANT_WRITE:                     // 7
                trigger_error('ScreenshotMgr::validateUpload - could not write temporary file to disk', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_EXTENSION:                      // 8
                trigger_error('ScreenshotMgr::validateUpload - a php extension stopped the file upload.', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
        }

        self::$fileName = $_FILES['screenshotfile']['tmp_name'];

        // points to invalid file
        if (!is_uploaded_file(self::$fileName))
        {
            trigger_error('ScreenshotMgr::validateUpload - uploaded file not in upload directory', E_USER_ERROR);
            self::$error    = Lang::main('intError');
            self::$fileName = '';
            return false;
        }

        // check if file is an image
        if (!self::setMimeType())
        {
            self::$error    = Lang::screenshot('error', 'unkFormat');
            self::$fileName = '';
            return false;
        }

        // invalid file
        if ($is = getimagesize(self::$fileName))
        {
            // image size out of bounds
            if ($is[0] < self::$MIN_SIZE || $is[1] < self::$MIN_SIZE)
                self::$error = Lang::screenshot('error', 'tooSmall');
            else if ($is[0] > self::MAX_W || $is[1] > self::MAX_H)
                self::$error = Lang::screenshot('error', 'selectSS');
        }
        else
            self::$error = Lang::screenshot('error', 'selectSS');

        if (!self::$error)
            return true;

        self::$fileName = '';
        return false;
    }

    public static function loadUpload() : bool
    {
        if (!self::$hasUpload)
            return false;

        return match (self::$mimeType)
        {
            self::MIME_JPG => self::loadFromJPG(),
            self::MIME_PNG => self::loadFromPNG(),
            default        => false
        };
    }

    public static function loadFile(string $path, string $nameBase) : bool
    {
        self::$fileName = sprintf($path, $nameBase);

        if (!file_exists(self::$fileName))
        {
            trigger_error('ScreenshotMgr::loadFile - image ('.self::$fileName.') not found', E_USER_ERROR);
            self::$fileName = '';
            return false;
        }

        return self::loadFromJPG();
    }

    public static function calcImgDimensions() : array
    {
        if (!self::$img)
            return [];

        $oSize = $rSize = [imagesx(self::$img), imagesy(self::$img)];
        $rel   = $oSize[0] / $oSize[1];

        // check for oversize and refit to crop-screen
        if ($rel >= 1.5 && $oSize[0] > self::LIMIT_W)
            $rSize = [self::LIMIT_W, self::LIMIT_W / $rel];
        else if ($rel < 1.5 && $oSize[1] > self::LIMIT_H)
            $rSize = [self::LIMIT_H * $rel, self::LIMIT_H];

        // r: resized; o: original
        // r: x <= 488 && y <= 325  while x proportional to y
        return array(
            'oWidth'  => $oSize[0],
            'rWidth'  => $rSize[0],
            'oHeight' => $oSize[1],
            'rHeight' => $rSize[1]
        );
    }

    public static function saveUpload(string $nameBase) : bool
    {
        if (!self::$img)
            return false;

        ['oWidth' => $oW, 'rWidth' => $rW, 'oHeight' => $oH, 'rHeight' => $rH] = self::calcImgDimensions();

        // use this image for work
        if (!self::writeImage(self::PATH_TEMP, $nameBase.'_original'))
            return false;

        // use this image to display in cropper
        $res = imagecreatetruecolor($rW, $rH);
        if (!$res)
        {
            trigger_error('ScreenshotMgr::saveUpload - imagecreate failed', E_USER_ERROR);
            return false;
        }

        if (!imagecopyresampled($res, self::$img, 0, 0, 0, 0, $rW, $rH, $oW, $oH))
        {
            trigger_error('ScreenshotMgr::saveUpload - imagecopy failed', E_USER_ERROR);
            return false;
        }

        self::$img = $res;
        unset($res);

        if (!self::writeImage(self::PATH_TEMP, $nameBase))
            return false;

        self::$img = null;

        return true;
    }

    public static function createThumbnail(string $fileName) : bool
    {
        if (!self::$img)
            return false;

        return self::resizeAndWrite(self::DIMS_THUMB[0], self::DIMS_THUMB[1], self::PATH_THUMB, $fileName);
    }

    public static function createResized(string $fileName) : bool
    {
        if (!self::$img)
            return false;

        return self::resizeAndWrite(self::DIMS_RESIZED[0], self::DIMS_RESIZED[1], self::PATH_RESIZED, $fileName);
    }

    public static function cropImg(float $scaleX, float $scaleY, float $scaleW, float $scaleH) : bool
    {
        if (!self::$img)
            return false;

        $x = (int)(imagesx(self::$img) * $scaleX);
        $y = (int)(imagesy(self::$img) * $scaleY);
        $w = (int)(imagesx(self::$img) * $scaleW);
        $h = (int)(imagesy(self::$img) * $scaleH);

        $destImg = imagecreatetruecolor($w, $h);
        if (!$destImg)
            return false;

     // imagefill($destImg, 0, 0, imagecolorallocate($destImg, 255, 255, 255));
        imagecopy($destImg, self::$img, 0, 0, $x, $y, $w, $h);

        self::$img = $destImg;
        imagedestroy($destImg);

        return true;
    }

    public static function writeImage(string $path, string $file) : bool
    {
        if (!self::$img)
            return false;

        if (imagejpeg(self::$img, sprintf($path, $file), 100))
            return true;

        trigger_error('ScreenshotMgr::writeImage - write failed', E_USER_ERROR);
        return false;
    }

    private static function setMimeType() : bool
    {
        if (!self::$hasUpload)
            return false;

        $mime = (new \finfo(FILEINFO_MIME))?->file(self::$fileName);

        if ($mime && stripos($mime, 'image/png') === 0)
            self::$mimeType = self::MIME_PNG;
        else if ($mime && preg_match('/^image\/jpe?g/i', $mime))
            self::$mimeType = self::MIME_JPG;
        else
            trigger_error('ScreenshotMgr::setMimeType - uploaded file is of type: '.$mime, E_USER_WARNING);

        return self::$mimeType != self::MIME_UNK;
    }

    private static function loadFromPNG() : bool
    {
        $image = imagecreatefrompng(self::$fileName);
        if (!$image)
            return false;

        self::$img = imagecreatetruecolor(imagesx($image), imagesy($image)) ?: null;
        if (!self::$img)
            return false;

     // imagefill(self::$img, 0, 0, imagecolorallocate(self::$img, 255, 255, 255));
        imagealphablending(self::$img, true);
        imagecopy(self::$img, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);

        return true;
    }

    private static function loadFromJPG() : bool
    {
        self::$img = imagecreatefromjpeg(self::$fileName) ?: null;

        return !is_null(self::$img);
    }

    private static function resizeAndWrite(int $limitW, int $limitH, string $path, string $file) : bool
    {
        $srcW = imagesx(self::$img);
        $srcH = imagesy(self::$img);

        // already small enough
        if ($srcW < $limitW && $srcH < $limitH)
            return true;

        $scale = min(1.0, $limitW / $srcW, $limitH / $srcH);
        $destW = $srcW * $scale;
        $destH = $srcH * $scale;

        $destImg = imagecreatetruecolor($destW, $destH);

     // imagefill($destImg, 0, 0, imagecolorallocate($destImg, 255, 255, 255));
        imagecopyresampled($destImg, self::$img, 0, 0, 0, 0, $destW, $destH, $srcW, $srcH);

        return imagejpeg($destImg, sprintf($path, $file), 100);
    }


    /*************/
    /* Admin Mgr */
    /*************/

    public static function getScreenshots(int $type = 0, int $typeId = 0, $userId = 0, ?int &$nFound = 0) : array
    {
        $screenshots = DB::Aowow()->select(
           'SELECT    s.`id`, a.`username` AS "user", s.`date`, s.`width`, s.`height`, s.`type`, s.`typeId`, s.`caption`, s.`status`, s.`status` AS "flags"
            FROM      ?_screenshots s
            LEFT JOIN ?_account a ON s.`userIdOwner` = a.`id`
            WHERE
                    { s.`type` = ?d }
                    { AND s.`typeId` = ?d }
                    { s.`userIdOwner` = ?d }
          { LIMIT    ?d }',
            $userId ? DBSIMPLE_SKIP : $type,
            $userId ? DBSIMPLE_SKIP : $typeId,
            $userId ? $userId : DBSIMPLE_SKIP,
            $userId || $type ? DBSIMPLE_SKIP : 100
        );

        $num = [];
        foreach ($screenshots as $s)
        {
            if (empty($num[$s['type']][$s['typeId']]))
                $num[$s['type']][$s['typeId']] = 1;
            else
                $num[$s['type']][$s['typeId']]++;
        }

        $nFound = 0;

        // format data to meet requirements of the js
        foreach ($screenshots as $i => &$s)
        {
            $nFound++;

            $s['date'] = date(Util::$dateFormatInternal, $s['date']);

            $s['name'] = "Screenshot #".$s['id'];           // what should we REALLY name it?

            if ($i > 0)
                $s['prev'] = $i - 1;

            if (($i + 1) < count($screenshots))
                $s['next'] = $i + 1;

            // order gives priority for 'status'
            if (!($s['flags'] & CC_FLAG_APPROVED))
            {
                $s['pending'] = 1;
                $s['status']  = self::STATUS_PENDING;
            }
            else
                $s['status'] = self::STATUS_APPROVED;

            if ($s['flags'] & CC_FLAG_STICKY)
            {
                $s['sticky'] = 1;
                $s['status'] = self::STATUS_STICKY;
            }

            if ($s['flags'] & CC_FLAG_DELETED)
            {
                $s['deleted'] = 1;
                $s['status'] = self::STATUS_DELETED;
            }

            // something todo with massSelect .. am i doing this right?
            if ($num[$s['type']][$s['typeId']] == 1)
                $s['unique'] = 1;

            if (!$s['user'])
                unset($s['user']);
        }

        return $screenshots;
    }

    public static function getPages(?bool $all, ?int &$nFound) : array
    {
        // i GUESS .. ss_getALL ? everything : pending
        $nFound = 0;
        $pages  = DB::Aowow()->select(
           'SELECT   s.`type`, s.`typeId`, COUNT(1) AS "count", MIN(s.`date`) AS "date"
            FROM     ?_screenshots s
          { WHERE    (s.`status` & ?d) = 0 }
            GROUP BY s.`type`, s.`typeId`',
            $all ? DBSIMPLE_SKIP : CC_FLAG_APPROVED | CC_FLAG_DELETED
        );

        if ($pages)
        {
            // limit to one actually existing type each
            foreach (array_unique(array_column($pages, 'type')) as $t)
            {
                $ids = [];
                foreach ($pages as $row)
                    if ($row['type'] == $t)
                        $ids[] = $row['typeId'];

                if (!$ids)
                    continue;

                $obj = Type::newList($t, [Cfg::get('SQL_LIMIT_NONE'), ['id', $ids]]);
                if (!$obj || $obj->error)
                    continue;

                foreach ($pages as &$p)
                    if ($p['type'] == $t)
                        if ($obj->getEntry($p['typeId']))
                            $p['name'] = $obj->getField('name', true);
            }

            foreach ($pages as &$p)
            {
                if (empty($p['name']))
                {
                    trigger_error('ScreenshotMgr::getPages - screenshot linked to nonexistent type/typeId combination: '.$p['type'].'/'.$p['typeId'], E_USER_NOTICE);
                    unset($p);
                }
                else
                {
                    $nFound   += $p['count'];
                    $p['date'] = date(Util::$dateFormatInternal, $p['date']);
                }
            }
        }

        return $pages;
    }
}

?>
