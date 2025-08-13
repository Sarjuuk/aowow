<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminScreenshotsActionListResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;

    protected array $expectedGET       = array(
        'all' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    protected function generate() : void
    {
        $pages = ScreenshotMgr::getPages($this->_get['all'], $nPages);
        $this->result  = 'ssm_screenshotPages = '.Util::toJSON($pages).";\n";
        $this->result .= 'ssm_numPagesFound = '.$nPages.';';
    }
}
