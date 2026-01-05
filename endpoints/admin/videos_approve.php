<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionApproveResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'id'  => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminVideosActionApproveResponse - videoId empty', E_USER_ERROR);
            return;
        }

        $viEntries = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `userIdOwner`, `date`, `type`, `typeId` FROM ?_videos WHERE (`status` & ?d) = 0 AND `id` IN (?a)', CC_FLAG_APPROVED, $this->_get['id']);
        foreach ($viEntries as $id => $viData)
        {
            // set as approved in DB
            DB::Aowow()->query('UPDATE ?_videos SET `status` = ?d, `userIdApprove` = ?d WHERE `id` = ?d', CC_FLAG_APPROVED, User::$id, $id);

            // gain siterep
            Util::gainSiteReputation($viData['userIdOwner'], SITEREP_ACTION_SUGGEST_VIDEO, ['id' => $id, 'what' => 1, 'date' => $viData['date']]);

            // flag DB entry as having videos
            if ($tbl = Type::getClassAttrib($viData['type'], 'dataTable'))
                DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_VIDEO, $viData['typeId']);

            unset($viEntries[$id]);
        }

        if (!$viEntries)
            trigger_error('AdminVideosActionApproveResponse - video(s) # '.implode(', ', array_keys($viEntries)).' not in db or already approved', E_USER_WARNING);
    }
}
