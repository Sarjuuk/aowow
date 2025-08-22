<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminSiteconfigActionAddResponse extends TextResponse
{
    protected int   $requiredUserGroup = U_GROUP_DEV | U_GROUP_ADMIN;

    protected array $expectedGET       = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Cfg::PATTERN_CONF_KEY_FULL]],
        'val' => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkTextBlob']          ]
    );

    protected function generate() : void
    {
        if (!$this->assertGET('key', 'val'))
        {
            trigger_error('AdminSiteconfigActionAddResponse - malformed request received', E_USER_ERROR);
            $this->result = Lang::main('intError');
            return;
        }

        $key = trim($this->_get['key']);
        $val = trim(urldecode($this->_get['val']));

        $this->result = Cfg::add($key, $val);
    }
}

?>
