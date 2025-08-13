<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionManageResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'type'   => ['filter' => FILTER_VALIDATE_INT                      ],
        'typeid' => ['filter' => FILTER_VALIDATE_INT                      ],
        'user'   => ['filter' => FILTER_CALLBACK, 'options' => 'urldecode']
    );

    protected function generate() : void
    {
        $res = [];

        if ($this->_get['type'] && $this->_get['typeid'])
            $res = ScreenshotMgr::getScreenshots($this->_get['type'], $this->_get['typeid']);
        else if ($this->_get['user'])
            if ($uId = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']))
                $res = ScreenshotMgr::getScreenshots(userId: $uId);

        $this->result =  'ssm_screenshotData = '.Util::toJSON($res);
    }
}
