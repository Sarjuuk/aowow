<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionDeleteResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    // 2 steps: 1) remove from sight, 2) remove from disk
    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminScreenshotsActionDeleteResponse - screenshotId empty', E_USER_ERROR);
            return;
        }

        foreach ($this->_get['id'] as $id)
        {
            // irrevocably purge files already flagged as deleted (should only exist as pending)
            if (User::isInGroup(U_GROUP_ADMIN) && DB::Aowow()->selectCell('SELECT 1 FROM ::screenshots WHERE `status` & %i AND `id` = %i', CC_FLAG_DELETED, $id))
            {
                DB::Aowow()->qry('DELETE FROM ::screenshots WHERE `id` = %i', $id);
                if (file_exists(sprintf(ScreenshotMgr::PATH_PENDING, $id)))
                    unlink(sprintf(ScreenshotMgr::PATH_PENDING, $id));

                continue;
            }

            // move normal to pending and remove resized and thumb
            if (file_exists(sprintf(ScreenshotMgr::PATH_NORMAL, $id)))
                rename(sprintf(ScreenshotMgr::PATH_NORMAL, $id), sprintf(ScreenshotMgr::PATH_PENDING, $id));

            if (file_exists(sprintf(ScreenshotMgr::PATH_THUMB, $id)))
                unlink(sprintf(ScreenshotMgr::PATH_THUMB, $id));

            if (file_exists(sprintf(ScreenshotMgr::PATH_RESIZED, $id)))
                unlink(sprintf(ScreenshotMgr::PATH_RESIZED, $id));
        }

        // flag as deleted if not aready
        $oldEntries = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, GROUP_CONCAT(`typeId`) FROM ::screenshots WHERE `id` IN %in GROUP BY `type`', $this->_get['id']);
        DB::Aowow()->qry('UPDATE ::screenshots SET `status` = %i, `userIdDelete` = %i WHERE `id` IN %in', CC_FLAG_DELETED, User::$id, $this->_get['id']);

        // deflag db entry as having screenshots
        foreach ($oldEntries as $type => $typeIds)
        {
            $typeIds  = explode(',', $typeIds);
            $toUnflag = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, IF(BIT_OR(`status`) & %i, 1, 0) AS "hasMore" FROM ::screenshots WHERE `type` = %i AND `typeId` IN %in GROUP BY `typeId` HAVING `hasMore` = 0', CC_FLAG_APPROVED, $type, $typeIds);
            if ($toUnflag && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                DB::Aowow()->qry('UPDATE %n SET cuFlags = cuFlags & ~%i WHERE id IN %in', $tbl, CUSTOM_HAS_SCREENSHOT, array_keys($toUnflag));
        }
    }
}
