<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideMgr
{
    public const /* int */ STATUS_NONE     = 0;
    public const /* int */ STATUS_DRAFT    = 1;
    public const /* int */ STATUS_REVIEW   = 2;
    public const /* int */ STATUS_APPROVED = 3;
    public const /* int */ STATUS_REJECTED = 4;
    public const /* int */ STATUS_ARCHIVED = 5;

    private const /* string */ IMG_DEST_DIR = 'static/uploads/guide/images/';
    private const /* string */ IMG_TMP_DIR  = 'static/uploads/temp/';

    public const VALID_URL     = '/^[a-z0-9_\-]{2,64}$/i';
    public const STATUS_COLORS = array(
        self::STATUS_DRAFT    => '#71D5FF',
        self::STATUS_REVIEW   => '#FFFF00',
        self::STATUS_APPROVED => '#1EFF00',
        self::STATUS_REJECTED => '#FF4040',
        self::STATUS_ARCHIVED => '#FFD100'
    );

    private static  array $ratingsStore = [];
    private static ?int   $imgUploadIdx = null;

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

        $ratings = DB::Aowow()->select('SELECT `entry` AS ARRAY_KEY, IFNULL(SUM(`value`), 0) AS "0", IFNULL(COUNT(*), 0) AS "1", IFNULL(MAX(IF(`userId` = ?d, `value`, 0)), 0) AS "2" FROM ?_user_ratings WHERE `type` = ?d AND `entry` IN (?a) GROUP BY `entry`', User::$id, RATING_GUIDE, $guideIds);
        foreach ($ratings as $id => [$total, $count, $self])
        {
            self::$ratingsStore[$id]['nvotes'] = (int)$count;
            self::$ratingsStore[$id]['_self']  = (int)$self;
            if ($count >= 5 )
                self::$ratingsStore[$id]['rating'] = $total / $count;
        }

        return self::$ratingsStore;
    }

    public static function handleUpload() : array
    {
        require_once('includes/libs/qqFileUploader.class.php');

        $tmpFile  = User::$username.'-'.Type::GUIDE.'-0-'.Util::createHash(16);

        $uploader = new \qqFileUploader(['jpg', 'jpeg', 'png'], 10 * 1024 * 1024);
        $result   = $uploader->handleUpload(self::IMG_TMP_DIR, $tmpFile, true);

        if (isset($result['error']))
            return $result;

        $mime = (new \finfo(FILEINFO_MIME))?->file(self::IMG_TMP_DIR . $result['newFilename']);

        if (!preg_match('/^image\/(png|jpe?g)/i', $mime, $m))
            return ['error' => Lang::screenshot('error', 'unkFormat')];

        // find next empty image name (an int)
        if (is_null(self::$imgUploadIdx))
        {
            if ($files = scandir(self::IMG_DEST_DIR, SCANDIR_SORT_DESCENDING))
                if (rsort($files, SORT_NATURAL) && $files[0] != '.' && $files[0] != '..')
                    $i = explode('.', $files[0])[0] + 1;

            self::$imgUploadIdx = $i ?? 1;
        }

        $targetFile = self::$imgUploadIdx . ($m[1] == 'png' ? '.png' : '.jpg');

        // move to final location
        if (!rename(self::IMG_TMP_DIR.$result['newFilename'], self::IMG_DEST_DIR.$targetFile))
        {
            trigger_error('GuideMgr::handleUpload - failed to move file', E_USER_ERROR);
            return ['error' => Lang::main('intError')];
        }

        return array(
            'success' => true,
            'id'      => self::$imgUploadIdx,
            'type'    => $m[1] == 'png' ? 3 : 2
        );
    }
}

?>
