<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionStickyResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;
    protected array $expectedGET       = array(
        'id'  => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminVideosActionStickyResponse - videoId empty', E_USER_ERROR);
            return;
        }

        // this one is a bit strange: as far as i've seen, the only thing a 'sticky' video does is show up in the infobox
        // this also means, that only one video per page should be sticky
        // so, handle it one by one and the last one affecting one particular type/typId-key gets the cake
        $viEntries = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `userIdOwner`, `date`, `type`, `typeId`, `status` FROM ?_videos WHERE (`status` & ?d) = 0 AND `id` IN (?a)', CC_FLAG_DELETED, $this->_get['id']);
        foreach ($viEntries as $id => $viData)
        {
            // approve yet unapproved videos
            if (!($viData['status'] & CC_FLAG_APPROVED))
            {
                // set as approved in DB
                DB::Aowow()->query('UPDATE ?_videos SET `status` = ?d, `userIdApprove` = ?d WHERE `id` = ?d', CC_FLAG_APPROVED, User::$id, $id);

                // gain siterep
                Util::gainSiteReputation($viData['userIdOwner'], SITEREP_ACTION_SUGGEST_VIDEO, ['id' => $id, 'what' => 1, 'date' => $viData['date']]);

                // flag DB entry as having videos
                if ($tbl = Type::getClassAttrib($viData['type'], 'dataTable'))
                    DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_VIDEO, $viData['typeId']);
            }

            // reset all others
            DB::Aowow()->query('UPDATE ?_videos a, ?_videos b SET a.`status` = a.`status` & ~?d WHERE a.`type` = b.`type` AND a.`typeId` = b.`typeId` AND a.`id` <> b.`id` AND b.`id` = ?d', CC_FLAG_STICKY, $id);

            // toggle sticky status
            DB::Aowow()->query('UPDATE ?_videos SET `status` = IF(`status` & ?d, `status` & ~?d, `status` | ?d) WHERE `id` = ?d AND `status` & ?d', CC_FLAG_STICKY, CC_FLAG_STICKY, CC_FLAG_STICKY, $id, CC_FLAG_APPROVED);

            unset($viEntries[$id]);
        }

        if ($viEntries)
            trigger_error('AdminVideosActionStickyResponse - video(s) # '.implode(', ', array_keys($viEntries)).' not in db or flagged as deleted', E_USER_WARNING);
    }
}
