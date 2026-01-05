<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionEdittitleResponse extends TextResponse
{
    use TrCommunityHelper;

    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'id'    => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkIdListUnsigned']]
    );
    protected array $expectedPOST      = array(
        'title' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            return;

        $caption = $this->handleCaption($this->_post['title']);

        DB::Aowow()->qry('UPDATE ::videos SET `caption` = %s WHERE `id` = %i', $caption, $this->_get['id'][0]);
    }
}
