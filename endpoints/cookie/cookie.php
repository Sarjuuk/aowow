<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class CookieBaseResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedGET   = array(
        'purge' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkEmptySet']]
    );

    public function __construct(private string $param)
    {
        // note that parent::__construct has to come after this
        if ($param && preg_match('/^[\w-]+$/i', $param))
            $this->expectedGET = [$param => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]];

        // NOW we know, what to expect and sanitize
        parent::__construct($param);
    }

    /* responses
        0: success
        $: silent error
    */
    protected function generate() : void
    {
        if (!$this->param && $this->_get['purge'])
        {
            if (User::$id && DB::Aowow()->query('UPDATE ?_account_cookies SET `data` = "purged" WHERE `userId` = ?d AND `name` LIKE "announcement-%"', User::$id) !== null)
                $this->result = 0;

            return;
        }

        if (!$this->param || !$this->assertGET($this->param))
        {
            trigger_error('CookieBaseResponse - malformed request received', E_USER_ERROR);
            return;
        }

        if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $this->param, $this->_get[$this->param]))
            $this->result = 0;
        else
            trigger_error('CookieBaseResponse - write to db failed', E_USER_ERROR);
    }
}

?>
