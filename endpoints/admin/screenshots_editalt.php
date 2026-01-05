<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionEditaltResponse extends TextResponse
{
    use TrCommunityHelper;

    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'id'  => ['filter' => FILTER_VALIDATE_INT]
    );
    protected array $expectedPOST      = array(
        'alt' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('id'))
            return;

        DB::Aowow()->qry('UPDATE ::screenshots SET `caption` = %s WHERE `id` = %i',
            $this->handleCaption($this->_post['alt']),
            $this->_get['id']
        );
    }
}
