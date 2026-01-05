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

        [$type, $oldTypeId] = array_values(DB::Aowow()->selectRow('SELECT `type`, `typeId` FROM ::screenshots WHERE `id` = %i', $this->_get['id']));
        $typeId             = $this->_get['typeid'];

        if (Type::validateIds($type, $typeId))
        {
            $tbl = Type::getClassAttrib($type, 'dataTable');

            // move screenshot
            DB::Aowow()->qry('UPDATE ::screenshots SET `typeId` = %i WHERE `id` = %i', $typeId, $this->_get['id']);

            // flag target as having screenshot
            DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` | %i WHERE `id` = %i', $tbl, CUSTOM_HAS_SCREENSHOT, $typeId);

            // deflag source for having had screenshots (maybe)
            $ssInfo = DB::Aowow()->selectRow('SELECT IF(BIT_OR(~`status`) & %i, 1, 0) AS "hasMore" FROM ::screenshots WHERE `status`& %i AND `type` = %i AND `typeId` = %i', CC_FLAG_DELETED, CC_FLAG_APPROVED, $type, $oldTypeId);
            if ($ssInfo || !$ssInfo['hasMore'])
                DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` & ~%i WHERE `id` = %i', $tbl, CUSTOM_HAS_SCREENSHOT, $oldTypeId);
        }
        else
            trigger_error('AdminScreenshotsActionRelocateResponse - invalid typeId #'.$typeId.' for type #'.$type, E_USER_ERROR);
    }
}
