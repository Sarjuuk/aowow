<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings form submit
 * write status to session and redirect to account settings
 */

class AccountUpdatepasswordResponse extends TextResponse
{
    protected ?string $redirectTo    = '?account#personal';
    protected  bool   $requiresLogin = true;

    protected  array  $expectedPOST  = array(
        'currentPassword' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']],
        'newPassword'     => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']],
        'confirmPassword' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']],
        'globalLogout'    => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkCheckbox']]
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

        if ($msg = $this->updatePassword())
            $_SESSION['msg'] = ['password', $this->success, $msg];
    }

    private function updatePassword() : string
    {
        if (!$this->assertPOST('currentPassword', 'newPassword', 'confirmPassword'))
            return Lang::main('intError');

        if (!Util::validatePassword($this->_post['newPassword'], $e))
            return $e == 1 ? Lang::account('errPassLength') : Lang::main('intError');

        if ($this->_post['newPassword'] !== $this->_post['confirmPassword'])
            return Lang::account('passMismatch');

        $userData = DB::Aowow()->selectRow('SELECT `status`, `passHash`, `statusTimer` FROM ?_account WHERE `id` = ?d', User::$id);
        if ($userData['status'] != ACC_STATUS_NONE && $userData['status'] != ACC_STATUS_CHANGE_PASS && $userData['statusTimer'] > time())
            return Lang::account('inputbox', 'error', 'isRecovering', [DateTime::formatTimeElapsedFloat(Cfg::get('ACC_RECOVERY_DECAY') * 1000)]);

        if (!User::verifyCrypt($this->_post['currentPassword'], $userData['passHash']))
            return Lang::account('wrongPass');

        if (User::verifyCrypt($this->_post['newPassword'], $userData['passHash']))
            return Lang::account('newPassDiff');

        $token = Util::createHash();

        // store new hash in updateValue field, exchange when confirmation mail gets confirmed
        if (!DB::Aowow()->query('UPDATE ?_account SET `updateValue` = ?, `status` = ?d, `statusTimer` = UNIX_TIMESTAMP() + ?d, `token` = ? WHERE `id` = ?d',
            User::hashCrypt($this->_post['newPassword']), ACC_STATUS_CHANGE_PASS, Cfg::get('ACC_RECOVERY_DECAY'), $token, User::$id))
            return Lang::main('intError');

        $email = DB::Aowow()->selectCell('SELECT `email` FROM ?_account WHERE `id` = ?d', User::$id);
        if (!Util::sendMail($email, 'update-password', [$token, $email], Cfg::get('ACC_RECOVERY_DECAY')))
            return Lang::main('intError2', ['send mail']);

        // logout all other active sessions
        if ($this->_post['globalLogout'])
            DB::Aowow()->query('UPDATE ?_account_sessions SET `status` = ?d, `touched` = ?d WHERE `userId` = ?d AND `sessionId` <> ? AND `status` = ?d', SESSION_FORCED_LOGOUT, time(), User::$id, session_id(), SESSION_ACTIVE);

        $this->success = true;
        return Lang::account('updateMessage', 'personal', [User::$email]);
    }
}

?>
