<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AccountSignoutResponse extends TextResponse
{
    use TrGetNext;

    protected array $expectedGET = array(
        'next' => ['filter' => FILTER_SANITIZE_URL, 'flags' => FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH]
    );

    public function __construct(string $pageParam)
    {
        // if the user not is logged in goto login page
        if (!User::isLoggedIn())
            $this->forwardToSignIn();

        parent::__construct($pageParam);
    }

    protected function generate() : void
    {
        DB::Aowow()->query('UPDATE ?_account_sessions SET `touched` = ?d, `status` = ?d WHERE `sessionId` = ?', time(), SESSION_LOGOUT, session_id());

        User::destroy();

        $this->redirectTo = $this->getNext(true);
    }
}

?>
