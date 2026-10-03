<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideMgr
{
    private const string IMG_DEST_DIR = 'static/uploads/guide/images/';
    private const string IMG_TMP_DIR  = 'static/uploads/temp/';

    public const int IMG_MAX_BYTES = 10 * 1024 * 1024;
    public const int IMG_MAX_DIMENSION = 4096;
    public const int IMG_MAX_PIXELS = 12_000_000;

    public const int    STATUS_NONE      = 0;
    public const int    STATUS_DRAFT     = 1;
    public const int    STATUS_REVIEW    = 2;
    public const int    STATUS_APPROVED  = 3;
    public const int    STATUS_REJECTED  = 4;
    public const int    STATUS_ARCHIVED  = 5;

    public const string VALID_URL        = '/^[a-z0-9_\-]{2,64}$/i';
    public const array  STATUS_COLORS    = array(
        self::STATUS_DRAFT    => '#71D5FF',
        self::STATUS_REVIEW   => '#FFFF00',
        self::STATUS_APPROVED => '#1EFF00',
        self::STATUS_REJECTED => '#FF4040',
        self::STATUS_ARCHIVED => '#FFD100'
    );

    private static  array $ratingsStore = [];

    public static function createDescription(string $text) : string
    {
        return Lang::trimTextClean(Markup::stripTags($text), 120);
    }

    public static function getRatings(array $guideIds) : array
    {
        if (!$guideIds)
            return [];

        if (array_keys(self::$ratingsStore) == $guideIds)
            return self::$ratingsStore;

        self::$ratingsStore = array_fill_keys($guideIds, ['nvotes' => 0, 'rating' => -1]);

        $ratings = DB::Aowow()->selectAssoc('SELECT `entry` AS ARRAY_KEY, IFNULL(SUM(`value`), 0) AS "0", IFNULL(COUNT(*), 0) AS "1", IFNULL(MAX(IF(`userId` = %i, `value`, 0)), 0) AS "2" FROM ::user_ratings WHERE `type` = %i AND `entry` IN %in GROUP BY `entry`', User::$id, RATING_GUIDE, $guideIds);
        foreach ($ratings as $id => [$total, $count, $self])
        {
            self::$ratingsStore[$id]['nvotes'] = (int)$count;
            self::$ratingsStore[$id]['_self']  = (int)$self;
            if ($count >= 5 )
                self::$ratingsStore[$id]['rating'] = $total / $count;
        }

        return self::$ratingsStore;
    }

    /** Validate bounded image bytes, re-encode pixels and publish a new exclusive numeric file; always clean staging. */
    public static function handleUpload() : array
    {
        if (!ContributionBudget::reserve('guide-image'))
            return ['error' => ContributionBudget::error()];
        require_once('includes/libs/qqFileUploader.class.php');
        $source = null;
        try
        {
            $uploader = new \qqFileUploader(['jpg', 'jpeg', 'png'], self::IMG_MAX_BYTES);
            $result = $uploader->handleUpload(self::IMG_TMP_DIR, 'guide-'.Util::createHash(24));
            if (isset($result['error']))
                return $result;
            $source = self::IMG_TMP_DIR.$result['newFilename'];
            $size = filesize($source);
            if (!$size || $size > self::IMG_MAX_BYTES)
                return ['error' => Lang::main('intError')];

            $mime = (new \finfo(FILEINFO_MIME_TYPE))->file($source);
            $dimensions = getimagesize($source);
            if (!$dimensions || !in_array($mime, ['image/jpeg', 'image/png'], true) ||
                $dimensions[2] !== ($mime === 'image/png' ? IMAGETYPE_PNG : IMAGETYPE_JPEG))
                return ['error' => Lang::screenshot('error', 'unkFormat')];
            [$width, $height, $type] = $dimensions;
            // Reject compressed image bombs before GD allocates a decoded canvas.
            if ($width <= 0 || $height <= 0 || $width > self::IMG_MAX_DIMENSION || $height > self::IMG_MAX_DIMENSION ||
                $width > intdiv(self::IMG_MAX_PIXELS, $height))
                return ['error' => Lang::screenshot('error', 'unkFormat')];

            $image = $type === IMAGETYPE_PNG ? imagecreatefrompng($source) : imagecreatefromjpeg($source);
            if (!$image)
                return ['error' => Lang::screenshot('error', 'unkFormat')];
            if ($type === IMAGETYPE_PNG)
                imagesavealpha($image, true);

            $id = self::saveImage($image, $type);
            if (!$id)
                return ['error' => Lang::main('intError')];
            return ['success' => true, 'id' => $id, 'type' => $type === IMAGETYPE_PNG ? 3 : 2];
        }
        catch (\Throwable)
        {
            return ['error' => Lang::main('intError')];
        }
        finally
        {
            if ($source && is_file($source) && !unlink($source))
                trigger_error('GuideMgr::handleUpload - staging cleanup failed', E_USER_WARNING);
        }
    }

    /** Only fresh GD output reaches public storage; exclusive creation never replaces an existing image or link. */
    private static function saveImage(\GdImage $image, int $type) : ?int
    {
        $encoded = tmpfile();
        if (!$encoded)
            return null;
        $target = null;
        $output = null;
        $complete = false;
        try
        {
            $written = $type === IMAGETYPE_PNG ? imagepng($image, $encoded, 6) : imagejpeg($image, $encoded, 85);
            $size = fstat($encoded)['size'];
            if (!$written || !$size || $size > self::IMG_MAX_BYTES)
                return null;

            for ($attempt = 0; $attempt < 16; ++$attempt)
            {
                // Keep the response ID exactly representable by the existing JavaScript URL builder.
                $id = random_int(1, min(PHP_INT_MAX, 9_007_199_254_740_991));
                $candidate = self::IMG_DEST_DIR.$id.($type === IMAGETYPE_PNG ? '.png' : '.jpg');
                $output = @fopen($candidate, 'xb');
                if ($output)
                {
                    $target = $candidate;
                    break;
                }
                if (!file_exists($candidate) && !is_link($candidate))
                    return null;
            }
            if (!$output)
                return null;

            rewind($encoded);
            if (stream_copy_to_stream($encoded, $output, self::IMG_MAX_BYTES + 1) !== $size || !fflush($output))
                return null;
            $complete = true;
            return $id;
        }
        finally
        {
            if (is_resource($output)) fclose($output);
            fclose($encoded);
            if (!$complete && $target && is_file($target) && !unlink($target))
                trigger_error('GuideMgr::saveImage - output cleanup failed', E_USER_WARNING);
        }
    }
}

?>
