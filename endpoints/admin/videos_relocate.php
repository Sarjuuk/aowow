<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionRelocateResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'id'     => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']],
        'typeid' => ['filter' => FILTER_VALIDATE_INT                                               ]
        // (but not type..?)
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id', 'typeid'))
        {
            trigger_error('AdminVideosActionRelocateResponse - videoId or typeId empty', E_USER_ERROR);
            return;
        }

        $id                 = $this->_get['id'][0];
        [$type, $oldTypeId] = array_values(DB::Aowow()->selectRow('SELECT `type`, `typeId` FROM ?_videos WHERE `id` = ?d', $id));
        $typeId             = $this->_get['typeid'];

        if (Type::validateIds($type, $typeId))
        {
            $tbl = Type::getClassAttrib($type, 'dataTable');

            // move video
            DB::Aowow()->query('UPDATE ?_videos SET `typeId` = ?d WHERE `id` = ?d', $typeId, $id);

            // flag target as having video
            DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` | ?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_VIDEO, $typeId);

            // deflag source for having had videos (maybe)
            $viInfo = DB::Aowow()->selectRow('SELECT IF(BIT_OR(~`status`) & ?d, 1, 0) AS "hasMore" FROM ?_videos WHERE `status`& ?d AND `type` = ?d AND `typeId` = ?d', CC_FLAG_DELETED, CC_FLAG_APPROVED, $type, $oldTypeId);
            if ($viInfo || !$viInfo['hasMore'])
                DB::Aowow()->query('UPDATE ?# SET `cuFlags` = `cuFlags` & ~?d WHERE `id` = ?d', $tbl, CUSTOM_HAS_VIDEO, $oldTypeId);
        }
        else
            trigger_error('AdminVideosActionRelocateResponse - invalid typeId #'.$typeId.' for type #'.$type, E_USER_ERROR);
    }
}
