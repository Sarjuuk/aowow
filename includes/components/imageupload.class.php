<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


abstract class ImageUpload
{
    public const /* int */ MIME_UNK  = 0;
    public const /* int */ MIME_JPG  = 1;
    public const /* int */ MIME_PNG  = 2;
    public const /* int */ MIME_WEBP = 3;

    // scale img down if larger than crop screen
    private const /* int */ CROP_W = 488;
    private const /* int */ CROP_H = 325;

    protected const /* int */ JPEG_QUALITY = 85;

    protected static  int      $mimeType        = self::MIME_UNK;
    protected static ?\GdImage $img             = null;
    protected static  string   $fileName        = '';
    protected static  string   $uploadFormField;
    protected static  string   $tmpPath;

    public static bool   $hasUpload = false;
    public static string $error     = '';

    public static function init() : bool
    {
        // active screenshot upload
        self::$hasUpload = $_FILES && !empty($_FILES[static::$uploadFormField]);

        return true;
    }

    public static function validateUpload() : bool
    {
        if (!self::$hasUpload)
            return false;

        switch ($_FILES[static::$uploadFormField]['error'])         // 0 is fine
        {
            case UPLOAD_ERR_INI_SIZE:                       // 1
            case UPLOAD_ERR_FORM_SIZE:                      // 2
                trigger_error('ImageUpload::validateUpload - the file exceeds the maximum size of '.ini_get('upload_max_filesize'), E_USER_WARNING);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_PARTIAL:                        // 3
                trigger_error('ImageUpload::validateUpload - upload was interrupted', E_USER_WARNING);
                self::$error = Lang::screenshot('error', 'selectSS');
                return false;
            case UPLOAD_ERR_NO_FILE:                        // 4
                trigger_error('ImageUpload::validateUpload - no file was received', E_USER_WARNING);
                self::$error = Lang::screenshot('error', 'selectSS');
                return false;
            case UPLOAD_ERR_NO_TMP_DIR:                     // 6
                trigger_error('ImageUpload::validateUpload - temporary upload directory is not set', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_CANT_WRITE:                     // 7
                trigger_error('ImageUpload::validateUpload - could not write temporary file to disk', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
            case UPLOAD_ERR_EXTENSION:                      // 8
                trigger_error('ImageUpload::validateUpload - a php extension stopped the file upload.', E_USER_ERROR);
                self::$error = Lang::main('intError');
                return false;
        }

        self::$fileName = $_FILES[static::$uploadFormField]['tmp_name'];

        // points to invalid file
        if (!is_uploaded_file(self::$fileName))
        {
            trigger_error('ImageUpload::validateUpload - uploaded file not in upload directory', E_USER_ERROR);
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
            self::MIME_JPG  => self::loadFromJPG(),
            self::MIME_PNG  => self::loadFromPNG(),
            self::MIME_WEBP => self::loadFromWEBP(),
            default         => false
        };
    }

    public static function loadFile(string $path, string $nameBase) : bool
    {
        self::$fileName = sprintf($path, $nameBase);

        if (!file_exists(self::$fileName))
        {
            trigger_error('ImageUpload::loadFile - image ('.self::$fileName.') not found', E_USER_ERROR);
            self::$fileName = '';
            return false;
        }

        // we are using only jpg internally
        return self::loadFromJPG();
    }

    public static function calcImgDimensions() : array
    {
        if (!self::$img)
            return [];

        $oSize = $rSize = [imagesx(self::$img), imagesy(self::$img)];
        $rel   = $oSize[0] / $oSize[1];

        // check for oversize and refit to crop-screen
        if ($rel >= 1.5 && $oSize[0] > self::CROP_W)
            $rSize = [self::CROP_W, self::CROP_W / $rel];
        else if ($rel < 1.5 && $oSize[1] > self::CROP_H)
            $rSize = [self::CROP_H * $rel, self::CROP_H];

        // r: resized; o: original
        // r: x <= 488 && y <= 325  while x proportional to y
        return array(
            'oWidth'  => $oSize[0],
            'rWidth'  => $rSize[0],
            'oHeight' => $oSize[1],
            'rHeight' => $rSize[1]
        );
    }

    public static function tempSaveUpload(array $tmpNameParts, ?string &$uid) : bool
    {
        if (!self::$img || !$tmpNameParts)
            return false;

        $uid = Util::createHash(16);

        $nameBase = User::$username.'-'.implode('-', $tmpNameParts).'-'.$uid;

        // use this image for work
        if (!self::writeImage(static::$tmpPath, $nameBase.'_original'))
            return false;

        ['oWidth' => $oW, 'rWidth' => $rW, 'oHeight' => $oH, 'rHeight' => $rH] = self::calcImgDimensions();

        // use this image to display in cropper
        $res = imagecreatetruecolor($rW, $rH);
        if (!$res)
        {
            trigger_error('ImageUpload::tempSaveUpload - imagecreate failed', E_USER_ERROR);
            return false;
        }

        if (!imagecopyresampled($res, self::$img, 0, 0, 0, 0, $rW, $rH, $oW, $oH))
        {
            trigger_error('ImageUpload::tempSaveUpload - imagecopy failed', E_USER_ERROR);
            return false;
        }

        self::$img = $res;
        unset($res);

        return self::writeImage(static::$tmpPath, $nameBase);
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

        if (imagejpeg(self::$img, sprintf($path, $file), self::JPEG_QUALITY))
            return true;

        trigger_error('ImageUpload::writeImage - write failed', E_USER_ERROR);
        return false;
    }

    private static function setMimeType() : bool
    {
        if (!self::$hasUpload)
            return false;

        $mime = (new \finfo(FILEINFO_MIME))?->file(self::$fileName);

        if ($mime && stripos($mime, 'image/png') === 0)
            self::$mimeType = self::MIME_PNG;
        else if ($mime && stripos($mime, 'image/webp') === 0)
            self::$mimeType = self::MIME_WEBP;
        else if ($mime && preg_match('/^image\/jpe?g/i', $mime))
            self::$mimeType = self::MIME_JPG;
        else
            trigger_error('ImageUpload::setMimeType - uploaded file is of type: '.$mime, E_USER_WARNING);

        return self::$mimeType != self::MIME_UNK;
    }

    private static function loadFromPNG() : bool
    {
        // straight self::$img = imagecreatefrompng(self::$fileName); causes issues when transforming the alpha channel
        // this roundabout way through imagealphablending() avoids that
        $image = imagecreatefrompng(self::$fileName);
        if (!$image)
            return false;

        self::$img = imagecreatetruecolor(imagesx($image), imagesy($image)) ?: null;
        if (!self::$img)
            return false;

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

    private static function loadFromWEBP() : bool
    {
        $image = imagecreatefromwebp(self::$fileName);
        if (!$image)
            return false;

        self::$img = imagecreatetruecolor(imagesx($image), imagesy($image)) ?: null;
        if (!self::$img)
            return false;

        imagealphablending(self::$img, true);
        imagecopy(self::$img, $image, 0, 0, 0, 0, imagesx($image), imagesy($image));
        imagedestroy($image);

        return true;
    }

    protected static function resizeAndWrite(int $limitW, int $limitH, string $path, string $file) : bool
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

        return imagejpeg($destImg, sprintf($path, $file), self::JPEG_QUALITY);
    }
}

?>
