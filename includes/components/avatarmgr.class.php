<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AvatarMgr extends ImageUpload
{
    private const MIN_SIZE = ICON_SIZE_LARGE;
    // 4k resolution
    private const MAX_W    = 4096;
    private const MAX_H    = 2160;

    public const STATUS_PENDING  = 0;                      // guessed
    public const STATUS_APPROVED = 1;                      // guessed
    public const STATUS_REJECTED = 2;

    protected static string $uploadFormField = 'iconfile';
    protected static string $tmpPath         = self::PATH_TEMP;

    public const PATH_TEMP    = 'static/uploads/temp/%s.jpg';
    public const PATH_AVATARS = 'static/uploads/avatars/%d.jpg';

    public static function init() : bool
    {
        $dirErr = false;
        foreach (['TEMP', 'AVATARS'] as $p)
        {
            $path = constant('self::PATH_' . $p);
            if (!is_writable(substr($path, 0, strrpos($path, '/'))))
            {
                trigger_error('AvatarMgr::init - directory '.substr($path, 0, strrpos($path, '/')).' not writable', E_USER_ERROR);
                $dirErr = true;
            }
        }

        if ($dirErr)
            return false;

        return parent::init();
    }

    public static function validateUpload() : bool
    {
        if (!parent::validateUpload())
            return false;

        // invalid file
        if ($is = getimagesize(self::$fileName))
        {
            // image size out of bounds
            if ($is[0] < ICON_SIZE_LARGE || $is[1] < ICON_SIZE_LARGE)
                self::$error = Lang::account('errTooSmall', [ICON_SIZE_LARGE]);
            else if ($is[0] > self::MAX_W || $is[1] > self::MAX_H)
                self::$error = Lang::account('selectAvatar');
        }
        else
            self::$error = Lang::account('selectAvatar');

        if (!self::$error)
            return true;

        self::$fileName = '';
        return false;
    }

    /* create icon texture atlas
     * ******************************
     * * LARGE          * MEDIUM    *
     * *                *           *
     * *                *           *
     * *                *************
     * *                * SMOL  *   *
     * *                *       *   *
     * *                *********   *
     * ******************************
     *
     * as static/uploads/avatars/<avatarIdx>.jpg
     */

    public static function createAtlas(string $fileName) : bool
    {
        if (!self::$img)
            return false;

        $sizes = [ICON_SIZE_LARGE, ICON_SIZE_MEDIUM, ICON_SIZE_SMALL];

        $dest = imagecreatetruecolor(ICON_SIZE_LARGE + ICON_SIZE_MEDIUM, ICON_SIZE_LARGE);
        $srcW = imagesx(self::$img);
        $srcH = imagesx(self::$img);

        $destX = $destY = 0;
        foreach ($sizes as $idx => $dim)
        {
            imagecopyresampled($dest, self::$img, $destX, $destY, 0, 0, $dim, $dim, $srcW, $srcH);

            if ($idx % 2)
                $destY += $dim;
            else
                $destX += $dim;
        }

        if (!imagejpeg($dest, sprintf(self::PATH_AVATARS, $fileName), self::JPEG_QUALITY))
            return false;

        self::$img = null;
        $dest = null;
        return true;
    }


    /*************/
    /* Admin Mgr */
    /*************/

    // unsure yet how that's supposed to work
    // for now pending uploads can be used right away
}

?>
