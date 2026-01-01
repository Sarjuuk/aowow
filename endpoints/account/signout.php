<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AccountSignoutResponse extends TextResponse
{
    use TrGetNext;

    protected array $expectedGET = array(
        'next'   => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[[:print:]]+$/']],
        'global' => ['filter' => FILTER_CALLBACK,        'options' => [self::class, 'checkEmptySet']  ]
    );

    public function __construct(string $rawParam)
    {
        // if the user not is logged in goto login page
        if (!User::isLoggedIn())
            $this->forwardToSignIn();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        if ($this->_get['global'])
            DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `status` = ?d WHERE `userId` = ?d', time(), SESSION_FORCED_LOGOUT, User::$id);
        else
            DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `status` = ?d WHERE `sessionId` = ?', time(), SESSION_LOGOUT, session_id());

        User::destroy();

        $this->redirectTo = $this->getNext(true);
    }
}

?>
