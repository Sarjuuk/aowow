<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class VideoMgr
{
    // as expected by js - this also makes the CC-flags functionally exclusive with each other
    private const STATUS_PENDING  = 0;
    private const STATUS_DELETED  = 999;
    private const STATUS_APPROVED = 100;
    private const STATUS_STICKY   = 105;

    public const TYPE_YOUTUBE = 1;                          // for in the grim darkness of the future, there is only youtube

    public const PATH_TEMP    = 'static/uploads/temp/%s';

    private static $tmpFile = '';

    public static function saveSuggestion(\stdClass $videoInfo, int $destType, int $destTypeId, ?string &$uid) : bool
    {
        $uid = Util::createHash(16);

        self::$tmpFile = sprintf(self::PATH_TEMP, User::$username.'-'.$destType.'-'.$destTypeId.'-'.$uid);

        $tmpFile = fopen(self::$tmpFile, 'w');
        if (!$tmpFile)
        {
            trigger_error('VideoMrg::saveSuggestion - failed to create temp file');
            return false;
        }

        fwrite($tmpFile, $videoInfo->id               . PHP_EOL);
        fwrite($tmpFile, $videoInfo->title            . PHP_EOL);
        fwrite($tmpFile, $videoInfo->thumbnail_url    . PHP_EOL);
        fwrite($tmpFile, $videoInfo->thumbnail_height . PHP_EOL);
        fwrite($tmpFile, $videoInfo->thumbnail_width  . PHP_EOL);

        return fclose($tmpFile);
    }

    public static function loadSuggestion(?\stdClass &$videoInfo, int $destType, int $destTypeId, ?string $uid) : bool
    {
        self::$tmpFile = sprintf(self::PATH_TEMP, User::$username.'-'.$destType.'-'.$destTypeId.'-'.$uid);

        if (!file_exists(self::$tmpFile))
            return false;

        if ($info = file(self::$tmpFile, FILE_IGNORE_NEW_LINES))
        {
            $videoInfo = new \stdClass;
            $videoInfo->id               = $info[0];
            $videoInfo->title            = $info[1];
            $videoInfo->thumbnail_url    = $info[2];
            $videoInfo->thumbnail_height = (int)$info[3];
            $videoInfo->thumbnail_width  = (int)$info[4];

            return true;
        }

        return false;
    }

    public static function dropTempFile()
    {
        if (!self::$tmpFile || !file_exists(self::$tmpFile))
            return;

        unlink(self::$tmpFile);
    }


    /*************/
    /* Admin Mgr */
    /*************/

    public static function getVideos(int $type = 0, int $typeId = 0, $userId = 0, ?int &$nFound = 0) : array
    {
        /* VideoData
         * caption: caption
         * date: isodate
         * height: ytPreviewImgHeight?
         * width: ytPreviewImgWidth?
         * id: id
         * next: idx || null
         * prev: idx || null
         * name: ytTitle?
         * pending: bool
         * status: statusCode
         * type: dbType
         * typeId: typeId
         * user: userName
         * url: ytPreviewImg?
         * videoType: always 1
         * videoId: videoId
         * unique: bool || null
         */

        $videos = DB::Aowow()->select(
           'SELECT    v.`id`, a.`username` AS "user", v.`date`, v.`videoId`, v.`type`, v.`typeId`, v.`caption`, v.`status` AS "flags", v.`url`, v.`name`
            FROM      ?_videos v
            LEFT JOIN ?_account a ON v.`userIdOwner` = a.`id`
            WHERE
                    { v.`type` = ?d }
                    { AND v.`typeId` = ?d }
                    { v.`userIdOwner` = ?d }
          { LIMIT    ?d }
            ORDER BY  `type`, `typeId`, `pos` ASC',
            $userId ? DBSIMPLE_SKIP : $type,
            $userId ? DBSIMPLE_SKIP : $typeId,
            $userId ? $userId : DBSIMPLE_SKIP,
            $userId || $type ? DBSIMPLE_SKIP : 100
        );

        $num = [];
        foreach ($videos as $v)
        {
            if (empty($num[$v['type']][$v['typeId']]))
                $num[$v['type']][$v['typeId']] = 1;
            else
                $num[$v['type']][$v['typeId']]++;
        }

        $nFound = 0;

        // format data to meet requirements of the js
        foreach ($videos as $i => &$v)
        {
            $nFound++;

            $v['date']      = date(Util::$dateFormatInternal, $v['date']);
            $v['videoType'] = self::TYPE_YOUTUBE;

            if ($i > 0)
                $v['prev'] = $i - 1;

            if (($i + 1) < count($videos))
                $v['next'] = $i + 1;

            // order gives priority for 'status'
            if (!($v['flags'] & CC_FLAG_APPROVED))
            {
                $v['pending'] = 1;
                $v['status']  = self::STATUS_PENDING;
            }
            else
                $v['status'] = self::STATUS_APPROVED;

            if ($v['flags'] & CC_FLAG_STICKY)
            {
                $v['sticky'] = 1;
                $v['status'] = self::STATUS_STICKY;
            }

            if ($v['flags'] & CC_FLAG_DELETED)
            {
                $v['deleted'] = 1;
                $v['status'] = self::STATUS_DELETED;
            }

            // something todo with massSelect .. am i doing this right?
            if ($num[$v['type']][$v['typeId']] == 1)
                $v['unique'] = 1;

            if (!$v['user'])
                unset($v['user']);
        }

        return $videos;
    }

    public static function getPages(?bool $all, ?int &$nFound) : array
    {
        // i GUESS .. vi_getALL ? everything : pending
        $nFound = 0;
        $pages  = DB::Aowow()->select(
           'SELECT   v.`type`, v.`typeId`, COUNT(1) AS "count", MIN(v.`date`) AS "date"
            FROM     ?_videos v
          { WHERE    (v.`status` & ?d) = 0 }
            GROUP BY v.`type`, v.`typeId`',
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
                    trigger_error('VideoMgr::getPages - video linked to nonexistent type/typeId combination: '.$p['type'].'/'.$p['typeId'], E_USER_NOTICE);
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
