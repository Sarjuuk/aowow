<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionOrderResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'id'   => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkIdListUnsigned'] ],
        'move' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => -1, 'max_range' => 1]] // -1 = up, 1 = down
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id', 'move') || $this->_get['move'] === 0)
        {
            trigger_error('AdminVideosActionOrderResponse - id or move empty', E_USER_ERROR);
            return;
        }

        $id = $this->_get['id'][0];

        $videos = DB::Aowow()->selectCol('SELECT a.`id` AS ARRAY_KEY, a.`pos` FROM ?_videos a, ?_videos b WHERE a.`type` = b.`type` AND a.`typeId` = b.`typeId` AND (a.`status` & ?d) = 0 AND b.`id` = ?d ORDER BY a.`pos` ASC', CC_FLAG_DELETED, $id);
        if (!$videos || count($videos) == 1)
        {
            trigger_error('AdminVideosActionOrderResponse - not enough videos to sort', E_USER_WARNING);
            return;
        }

        $dir    = $this->_get['move'];
        $curPos = $videos[$id];

        if ($dir == -1 && $curPos == 0)
        {
            trigger_error('AdminVideosActionOrderResponse - video #'.$id.' already in top position', E_USER_WARNING);
            return;
        }

        if ($dir == 1 && $curPos + 1 == count($videos))
        {
            trigger_error('AdminVideosActionOrderResponse - video #'.$id.' already in bottom position', E_USER_WARNING);
            return;
        }

        $oldKey = array_search($curPos + $dir, $videos);
        $videos[$oldKey] -= $dir;
        $videos[$id]     += $dir;

        foreach ($videos as $id => $pos)
            DB::Aowow()->query('UPDATE ?_videos SET `pos` = ?d WHERE `id` = ?d', $pos, $id);
    }
}
