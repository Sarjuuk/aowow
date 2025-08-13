<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionManageResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'type'   => ['filter' => FILTER_VALIDATE_INT                      ],
        'typeid' => ['filter' => FILTER_VALIDATE_INT                      ],
        'user'   => ['filter' => FILTER_CALLBACK, 'options' => 'urldecode']
    );

    protected function generate() : void
    {
        $res = [];

        if ($this->_get['type'] && $this->_get['typeid'])
            $res = VideoMgr::getVideos($this->_get['type'], $this->_get['typeid']);
        else if ($this->_get['user'])
            if ($uId = DB::Aowow()->selectCell('SELECT `id` FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_get['user']))
                $res = VideoMgr::getVideos(userId: $uId);

        $this->result =  'vim_videoData = '.Util::toJSON($res);
    }
}
