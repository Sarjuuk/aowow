<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminVideosActionListResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_VIDEO;

    protected array $expectedGET       = array(
        'all' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    protected function generate() : void
    {
        $pages = VideoMgr::getPages($this->_get['all'], $nPages);
        $this->result  = 'vim_videoPages = '.Util::toJSON($pages).";\n";
        $this->result .= 'vim_numPagesFound = '.$nPages.';';
    }
}
