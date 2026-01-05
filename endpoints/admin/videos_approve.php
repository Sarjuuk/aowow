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

        $viEntries = DB::Aowow()->selectAssoc('SELECT `id` AS ARRAY_KEY, `userIdOwner`, `date`, `type`, `typeId` FROM ::videos WHERE (`status` & %i) = 0 AND `id` IN %in', CC_FLAG_APPROVED, $this->_get['id']);
        foreach ($viEntries as $id => $viData)
        {
            // set as approved in DB
            DB::Aowow()->qry('UPDATE ::videos SET `status` = %i, `userIdApprove` = %i WHERE `id` = %i', CC_FLAG_APPROVED, User::$id, $id);

            // gain siterep
            Util::gainSiteReputation($viData['userIdOwner'], SITEREP_ACTION_SUGGEST_VIDEO, ['id' => $id, 'what' => 1, 'date' => $viData['date']]);

            // flag DB entry as having videos
            if ($tbl = Type::getClassAttrib($viData['type'], 'dataTable'))
                DB::Aowow()->qry('UPDATE %n SET `cuFlags` = `cuFlags` | %i WHERE `id` = %i', $tbl, CUSTOM_HAS_VIDEO, $viData['typeId']);

            unset($viEntries[$id]);
        }

        if (!$viEntries)
            trigger_error('AdminVideosActionApproveResponse - video(s) # '.implode(', ', array_keys($viEntries)).' not in db or already approved', E_USER_WARNING);
    }
}
