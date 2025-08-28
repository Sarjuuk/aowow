<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings form submit
 * write status to session and redirect to account settings
 */

class AccountUpdatecommunitysettingsResponse extends TextResponse
{
    protected ?string $redirectTo    = '?account#community';
    protected  bool   $requiresLogin = true;

    protected  array  $expectedPOST  = array(
        'desc' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextBlob']]
    );

    private bool $success = false;

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($message = $this->updateSettings())
            $_SESSION['msg'] = ['community', $this->success, $message];
    }

    protected function updateSettings()
    {
        if (is_null($this->_post['desc']))                  // assertPOST tests for empty string which is valid here
            return Lang::main('genericError');

        // description - 0 modified rows is still success
        if (!is_int(DB::Aowow()->query('UPDATE ?_account SET `description` = ? WHERE `id` = ?d', $this->_post['desc'], User::$id)))
            return Lang::main('genericError');

        $this->success = true;
        return Lang::account('updateMessage', 'community');
    }
}

?>
