<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/************
* get Community Content
************/

class CommunityContent
{
    /* todo: administration of content */

    private function getComments($type, $typeId)
    {
        // comments
        return array();
    }

    private function getVideos($type, $typeId)
    {
        return DB::Aowow()->Query("
            SELECT
                v.Id, 
                a.displayName AS user,
                v.date,
                v.videoId,
                v.caption,
                IF(v.status & 0x4, 1, 0) AS 'sticky'
            FROM
                ?_videos v,
                ?_account a
            WHERE
                v.type = ? AND v.typeId = ? AND v.status & 0x2",
            $type,
            $typeId
        );
    }

    private function getScreenshots($type, $typeId)
    {
        return DB::Aowow()->Query("
            SELECT
                s.Id,
                a.displayName AS user,
                s.date,
                s.width,
                s.height,
                s.caption,
                IF(s.status & 0x4, 1, 0) AS 'sticky'
            FROM
                ?_screenshots s,
                ?_account a
            WHERE
                s.type = ? AND s.typeId = ? AND s.status & 0x2",
            $type,
            $typeId
        );
    }

    public function getAll($type, $typeId)
    {
        return array(
            'vi' => self::getVideos($type, $typeId),
            'sc' => self::getScreenshots($type, $typeId),
            'co' => self::getComments($type, $typeId)
        );
    }
}
?>
