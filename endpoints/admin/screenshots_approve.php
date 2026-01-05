<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionApproveResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'id' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
        {
            trigger_error('AdminScreenshotsActionApproveResponse - screenshotId empty', E_USER_ERROR);
            return;
        }

        ScreenshotMgr::init();

        // create resized and thumb version of screenshot
        $ssEntries = DB::Aowow()->select('SELECT `id` AS ARRAY_KEY, `userIdOwner`, `date`, `type`, `typeId` FROM ?_screenshots WHERE (`status` & ?d) = 0 AND `id` IN (?a)', CC_FLAG_APPROVED, $this->_get['id']);
        foreach ($ssEntries as $id => $ssData)
        {
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

            unset($ssEntries[$id]);
        }

        if (!$ssEntries)
            trigger_error('AdminScreenshotsActionApproveResponse - screenshot(s) # '.implode(', ', array_keys($ssEntries)).' not in db or already approved', E_USER_WARNING);
    }
}
