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
            DB::Aowow()->selectCell('SELECT 1 FROM ::videos WHERE `status` & %i AND `id` IN %in', CC_FLAG_DELETED, $this->_get['id']);

        // flag as deleted if not aready
        $oldEntries = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, GROUP_CONCAT(`typeId`) FROM ::videos WHERE `id` IN %in GROUP BY `type`', $this->_get['id']);
        DB::Aowow()->qry('UPDATE ::videos SET `status` = %i, `userIdDelete` = %i WHERE (`status` & %i) = 0 AND `id` IN %in', CC_FLAG_DELETED, User::$id, CC_FLAG_DELETED, $this->_get['id']);

        // deflag db entry as having videos
        foreach ($oldEntries as $type => $typeIds)
        {
            $typeIds  = explode(',', $typeIds);
            $toUnflag = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(BIT_OR(`status`) & %i, 1, 0) AS "hasMore" FROM ::videos WHERE `type` = %i AND `typeId` IN %in GROUP BY `typeId` HAVING `hasMore` = 0', CC_FLAG_APPROVED, $type, $typeIds);
            if ($toUnflag && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                DB::Aowow()->qry('UPDATE %n SET cuFlags = cuFlags & ~%i WHERE id IN %in', $tbl, CUSTOM_HAS_VIDEO, array_keys($toUnflag));
        }
    }
}
