<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionDeleteResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'all'    => ['filter' => FILTER_CALLBACK,    'options' => [self::class, 'checkEmptySet']]
    );

    // 2 steps: 1) remove from sight, 2) remove from disk
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminVideosActionDeleteResponse - videoId empty', E_USER_ERROR);
            return;
        }

        // irrevocably purge files already flagged as deleted (should only exist as pending)
        if (User::isInGroup(U_GROUP_ADMIN))
            DB::Aowow()->selectCell('SELECT 1 FROM ?_videos WHERE `status` & ?d AND `id` IN (?a)', CC_FLAG_DELETED, $this->_get['id']);

        // flag as deleted if not aready
        $oldEntries = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, GROUP_CONCAT(`typeId`) FROM ?_videos WHERE `id` IN (?a) GROUP BY `type`', $this->_get['id']);
        DB::Aowow()->query('UPDATE ?_videos SET `status` = ?d, `userIdDelete` = ?d WHERE (`status` & ?d) = 0 AND `id` IN (?a)', CC_FLAG_DELETED, User::$id, CC_FLAG_DELETED, $this->_get['id']);

        // deflag db entry as having videos
        foreach ($oldEntries as $type => $typeIds)
        {
            $typeIds  = explode(',', $typeIds);
            $toUnflag = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(BIT_OR(`status`) & ?d, 1, 0) AS "hasMore" FROM ?_videos WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `typeId` HAVING `hasMore` = 0', CC_FLAG_APPROVED, $type, $typeIds);
            if ($toUnflag && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                DB::Aowow()->query('UPDATE ?# SET cuFlags = cuFlags & ~?d WHERE id IN (?a)', $tbl, CUSTOM_HAS_VIDEO, array_keys($toUnflag));
        }
    }
}
