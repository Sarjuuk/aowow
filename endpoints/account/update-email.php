<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings form submit
 * write status to session and redirect to account settings
 */

class AccountUpdateemailResponse extends TextResponse
{
    protected ?string $redirectTo    = '?account#personal';
    protected  bool   $requiresLogin = true;

    protected  array  $expectedPOST  = array(
        'newemail' => ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_FLAG_STRIP_AOWOW]
    );

    private bool $success = false;

    public function __construct(string $rawParam)
    {
        if (Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            (new TemplateResponse())->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($msg = $this->updateMail())
            $_SESSION['msg'] = ['email', $this->success, $msg];
    }

    private function updateMail() : string
    {
        // no input yet
        if (is_null($this->_post['newemail']))
            return Lang::main('intError');
        // truncated due to validation fail
        if (!$this->_post['newemail'])
            return Lang::account('emailInvalid');

        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE `email` = ? AND `id` <> ?d', $this->_post['newemail'], User::$id))
            return Lang::account('mailInUse');

        $status = DB::Aowow()->selectCell('SELECT `status` FROM ?_account WHERE `statusTimer` > UNIX_TIMESTAMP() AND `id` = ?d', User::$id);
        if ($status != ACC_STATUS_NONE && $status != ACC_STATUS_CHANGE_EMAIL)
            return Lang::account('inputbox', 'error', 'isRecovering', [DateTime::formatTimeElapsedFloat(Cfg::get('ACC_RECOVERY_DECAY') * 1000)]);

        $oldEmail = DB::Aowow()->selectCell('SELECT `email` FROM ?_account WHERE `id` = ?d', User::$id);
        if ($this->_post['newemail'] == $oldEmail)
            return Lang::account('newMailDiff');

        $token = Util::createHash();

        // store new mail in updateValue field, exchange when confirmation mail gets confirmed
        if (!DB::Aowow()->query('UPDATE ?_account SET `updateValue` = ?, `status` = ?d, `statusTimer` = UNIX_TIMESTAMP() + ?d, `token` = ? WHERE `id` = ?d',
            $this->_post['newemail'], ACC_STATUS_CHANGE_EMAIL, Cfg::get('ACC_RECOVERY_DECAY'), $token, User::$id))
            return Lang::main('intError');

        if (!Util::sendMail($this->_post['newemail'], 'change-email', [$token, $this->_post['newemail']], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);

        if (!Util::sendMail($oldEmail, 'revert-email', [$token, $oldEmail], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);

        $this->success = true;
        return Lang::account('updateMessage', 'personal', [$this->_post['newemail']]);
    }
}

?>
