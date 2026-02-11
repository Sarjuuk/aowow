<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminWeightpresetsActionSaveResponse extends TextResponse
{
    private const /* int */ ERR_NONE          = 0;
    private const /* int */ ERR_WRITE_DB      = 1;
    private const /* int */ ERR_WRITE_FILE    = 2;
    private const /* int */ ERR_MISCELLANEOUS = 999;

    protected int   $requiredUserGroup = U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_BUREAU;
    protected array $expectedPOST      = array(
        'id'     => ['filter' => FILTER_VALIDATE_INT                                                          ],
        '__icon' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => Cfg::PATTERN_CONF_KEY_FULL]],
        'scale'  => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkScale']             ]
    );

    protected function generate() : void
    {
        if (!$this->assertPOST('id', '__icon', 'scale'))
        {
            trigger_error('AdminWeightpresetsActionSaveResponse - malformed request received', E_USER_ERROR);
            $this->result = self::ERR_MISCELLANEOUS;
            return;
        }

        // save to db
        DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE `id` = ?d', $this->_post['id']);
        DB::Aowow()->query('UPDATE ?_account_weightscales SET `icon`= ? WHERE `id` = ?d', $this->_post['__icon'], $this->_post['id']);

        foreach (explode(',', $this->_post['scale']) as $s)
        {
            [$k, $v] = explode(':', $s);

            if (!Stat::getWeightJson($k) || $v < 1)
                continue;

            if (DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $this->_post['id'], $k, $v) === null)
            {
                trigger_error('AdminWeightpresetsActionSaveResponse - failed to write to database', E_USER_ERROR);
                $this->result = self::ERR_WRITE_DB;
                return;
            }
        }

        // write dataset
        exec('php aowow --build=weightPresets', $out);
        foreach ($out as $o)
            if (strstr($o, 'ERR'))
            {
                trigger_error('AdminWeightpresetsActionSaveResponse - failed to write dataset' . $o, E_USER_ERROR);
                $this->result = self::ERR_WRITE_FILE;
                return;
            }

        // all done
        $this->result = self::ERR_NONE;
    }

    protected static function checkScale(string $val) : string
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return $val;

        return '';
    }
}

?>
