<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionStickyResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminScreenshotsActionStickyResponse - screenshotId empty', E_USER_ERROR);
            return;
        }

        // this one is a bit strange: as far as i've seen, the only thing a 'sticky' screenshot does is show up in the infobox
        // this also means, that only one screenshot per page should be sticky
        // so, handle it one by one and the last one affecting one particular type/typId-key gets the cake
        $ssEntries = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `userIdOwner`, `date`, `type`, `typeId`, `status` FROM ?_screenshots WHERE (`status` & ?d) = 0 AND `id` IN (?a)', CC_FLAG_DELETED, $this->_get['id']);
        foreach ($ssEntries as $id => $ssData)
        {
            // approve yet unapproved screenshots
            if (!($ssData['status'] & CC_FLAG_APPROVED))
            {
                ScreenshotMgr::init();

                if (!ScreenshotMgr::loadFile(ScreenshotMgr::PATH_PENDING, $id))
                    continue;

                if (!ScreenshotMgr::createResized($id))
                    continue;

                if (!ScreenshotMgr::createThumbnail($id))
                    continue;

                // move pending > normal
                if (!rename(sprintf(ScreenshotMgr::PATH_PENDING, $id), sprintf(ScreenshotMgr::PATH_NORMAL, $id)))
                    continue;

                // set as approved in DB
                DB::Aowow()->query('UPDATE ?_screenshots SET `status` = ?d, `userIdApprove` = ?d WHERE `id` = ?d', CC_FLAG_APPROVED, User::$id, $id);

                // gain siterep
                Util::gainSiteReputation($ssData['userIdOwner'], SITEREP_ACTION_SUBMIT_SCREENSHOT, ['id' => $id, 'what' => 1, 'date' => $ssData['date']]);

                // flag DB entry as having screenshots
                if ($tbl = Type::getClassAttrib($ssData['type'], 'dataTable'))
                    DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_SCREENSHOT, $ssData['typeId']);
            }

            // reset all others
            DB::Aowow()->query('UPDATE ?_screenshots a, ?_screenshots b SET a.`status` = a.`status` & ~?d WHERE a.`type` = b.`type` AND a.`typeId` = b.`typeId` AND a.`id` <> b.`id` AND b.`id` = ?d', CC_FLAG_STICKY, $id);

            // toggle sticky status
            DB::Aowow()->query('UPDATE ?_screenshots SET `status` = IF(`status` & ?d, `status` & ~?d, `status` | ?d) WHERE `id` = ?d AND `status` & ?d', CC_FLAG_STICKY, CC_FLAG_STICKY, CC_FLAG_STICKY, $id, CC_FLAG_APPROVED);

            unset($ssEntries[$id]);
        }

        if ($ssEntries)
            trigger_error('AdminScreenshotsActionStickyResponse - screenshot(s) # '.implode(', ', array_keys($ssEntries)).' not in db or flagged as deleted', E_USER_WARNING);
    }
}
