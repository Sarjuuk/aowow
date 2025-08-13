<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionRelocateResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT],
        'typeid' => ['filter' => FILTER_VALIDATE_INT]
        // (but not type..?)
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id', 'typeid'))
        {
            trigger_error('AdminScreenshotsActionRelocateResponse - screenshotId or typeId empty', E_USER_ERROR);
            return;
        }

        [$type, $oldTypeId] = array_values(DB::Aowow()->selectRow('SELECT `type`, `typeId` FROM ?_screenshots WHERE `id` = ?d', $this->_get['id']));
        $typeId             = $this->_get['typeid'];

        if (Type::validateIds($type, $typeId))
        {
            $tbl = Type::getClassAttrib($type, 'dataTable');

            // move screenshot
            DB::Aowow()->query('UPDATE ?_screenshots SET `typeId` = ?d WHERE `id` = ?d', $typeId, $this->_get['id']);

            // flag target as having screenshot
            DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_SCREENSHOT, $typeId);

            // deflag source for having had screenshots (maybe)
            $ssInfo = DB::Aowow()->selectRow('SELECT IF(BIT_OR(~`status`) & ?d, 1, 0) AS "hasMore" FROM ?_screenshots WHERE `status`& ?d AND `type` = ?d AND `typeId` = ?d', CC_FLAG_DELETED, CC_FLAG_APPROVED, $type, $oldTypeId);
            if ($ssInfo || !$ssInfo['hasMore'])
                DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` & ~?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_SCREENSHOT, $oldTypeId);
        }
        else
            trigger_error('AdminScreenshotsActionRelocateResponse - invalid typeId #'.$typeId.' for type #'.$type, E_USER_ERROR);
    }
}
