<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via links on signin form and from recovery email
 *
 * A) redirect to external page
 * B) 1. click password reset link > display email form
 *    2. submit email form > send mail with recovery link
 *    3. click recovery link from mail > display password reset form
 *    4. submit password reset form > update password
 */

class AccountforgotpasswordResponse extends TemplateResponse
{
    use TrRecoveryHelper, TrGetNext;

    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'forgot-password';

    protected array  $expectedPOST = array(
        'email' => ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_FLAG_STRIP_AOWOW]
    );

    private bool $success = false;

    public function __construct(string $rawParam)
    {
        // don't redirect logged in users
        // you can be forgetful AND logged in

        if (Cfg::get('ACC_EXT_RECOVER_URL'))
            $this->forward(Cfg::get('ACC_EXT_RECOVER_URL'));

        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->title[] = Lang::account('title');

        parent::generate();

        $msg = $this->processMailForm();

        if ($this->success)
            $this->inputbox = ['inputbox-status', ['head' => Lang::account('inputbox', 'head', 'recoverPass', [1.5]), 'message' => $msg]];
        else
            $this->inputbox = ['inputbox-form-email', array(
                'head'   => Lang::account('inputbox', 'head', 'recoverPass', [1]),
                'error'  => $msg,
                'action' => '?account=forgot-password&next='.$this->getNext(),
                'email'  => $this->_post['email'] ?? ''
            )];
    }

    private function processMailForm() : string
    {
        // no input yet. show clean email form
        if (is_null($this->_post['email']))
            return '';

        // truncated due to validation fail
        if (!$this->_post['email'])
            return Lang::account('emailInvalid');

        $timeout = DB::Aowow()->selectCell('SELECT `unbanDate` FROM ?_account_bannedips WHERE `ip` = ? AND `type` = ?d AND `count` > ?d AND `unbanDate` > UNIX_TIMESTAMP()', User::$ip, IP_BAN_TYPE_PASSWORD_RECOVERY, Cfg::get('ACC_FAILED_AUTH_COUNT'));

        // on cooldown pretend we dont know the email address
        if ($timeout && $timeout > time())
            return Cfg::get('DEBUG') ? 'resend on cooldown: '.DateTime::formatTimeElapsed($timeout * 1000).' remaining' : Lang::account('inputbox', 'error', 'emailNotFound');

        // pretend recovery started
        if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE `email` = ?', $this->_post['email']))
        {
            // do not confirm or deny existence of email
            $this->success = !Cfg::get('DEBUG');
            return Cfg::get('DEBUG') ? Lang::account('inputbox', 'error', 'emailNotFound') : Lang::account('inputbox', 'message', 'recovPassSent', [$this->_post['email']]);
        }

        // recovery actually started
        if ($err = $this->startRecovery(ACC_STATUS_RECOVER_PASS, 'reset-password', $this->_post['email']))
            return $err;

        DB::Aowow()->query('INSERT INTO ?_account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (?, ?d, ?d, UNIX_TIMESTAMP() + ?d) ON DUPLICATE KEY UPDATE `count` = `count` + ?d, `unbanDate` = UNIX_TIMESTAMP() + ?d',
            User::$ip, IP_BAN_TYPE_PASSWORD_RECOVERY, Cfg::get('ACC_FAILED_AUTH_COUNT') + 1, Cfg::get('ACC_FAILED_AUTH_COUNT'), Cfg::get('ACC_FAILED_AUTH_BLOCK'), Cfg::get('ACC_FAILED_AUTH_BLOCK'));

        $this->success = true;
        return Lang::account('inputbox', 'message', 'recovPassSent', [$this->_post['email']]);
    }
}

?>
