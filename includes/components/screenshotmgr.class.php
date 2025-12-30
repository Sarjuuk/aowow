<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class ScreenshotMgr extends ImageUpload
{
    // config value
    public static int $MIN_SIZE;                            // 200
    // 4k resolution
    private const /* int */ MAX_W = 4096;
    private const /* int */ MAX_H = 2160;

    // as expected by js - this also makes the CC-flags functionally exclusive with each other
    private const /* int */ STATUS_PENDING  = 0;
    private const /* int */ STATUS_DELETED  = 999;
    private const /* int */ STATUS_APPROVED = 100;
    private const /* int */ STATUS_STICKY   = 105;

    private const /* array */ DIMS_RESIZED = [772, 618];
    private const /* array */ DIMS_THUMB   = [150, 150];

    protected static string $uploadFormField = 'screenshotfile';
    protected static string $tmpPath         = self::PATH_TEMP;

    public const /* string */ PATH_TEMP    = 'static/uploads/screenshots/temp/%s.jpg';
    public const /* string */ PATH_PENDING = 'static/uploads/screenshots/pending/%d.jpg';
    public const /* string */ PATH_THUMB   = 'static/uploads/screenshots/thumb/%d.jpg';
    public const /* string */ PATH_RESIZED = 'static/uploads/screenshots/resized/%d.jpg';
    public const /* string */ PATH_NORMAL  = 'static/uploads/screenshots/normal/%d.jpg';

    public static function init() : bool
    {
        self::$MIN_SIZE = Cfg::get('SCREENSHOT_MIN_SIZE');

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

    public static function createThumbnail(string $fileName) : bool
    {
        if (!self::$img)
            return false;

        return static::resizeAndWrite(self::DIMS_THUMB[0], self::DIMS_THUMB[1], self::PATH_THUMB, $fileName);
    }

    public static function createResized(string $fileName) : bool
    {
        if (!self::$img)
            return false;

        return self::resizeAndWrite(self::DIMS_RESIZED[0], self::DIMS_RESIZED[1], self::PATH_RESIZED, $fileName);
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

                $obj = Type::newList($t, [['id', $ids]]);
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
