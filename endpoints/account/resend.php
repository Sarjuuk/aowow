<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via link on login page
 * empty page with status box
 */

class AccountResendResponse extends TemplateResponse
{
    protected string $template     = 'text-page-generic';
    protected string $pageName     = 'resend';

    protected array  $expectedPOST = array(
        'email' => ['filter' => FILTER_VALIDATE_EMAIL, 'flags' => FILTER_FLAG_STRIP_AOWOW]
    );

    private bool $success = false;

    public function __construct(string $rawParam)
    {
        if (Cfg::get('ACC_EXT_RECOVER_URL'))
            $this->forward(Cfg::get('ACC_EXT_RECOVER_URL'));

        if (!Cfg::get('ACC_ALLOW_REGISTER') || Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        $this->title[] = Lang::account('title');

        parent::generate();

        // error from account=activate
        if (isset($_SESSION['error']['activate']))
        {
            $msg = $_SESSION['error']['activate'];
            unset($_SESSION['error']['activate']);
        }
        else
            $msg = $this->resend();

        if ($this->success)
            $this->inputbox = ['inputbox-status', ['head' => Lang::account('inputbox', 'head', 'resendMail'), 'message' => $msg]];
        else
            $this->inputbox = ['inputbox-form-email', array(
                'head'    => Lang::account('inputbox', 'head', 'resendMail'),
                'message' => Lang::account('inputbox', 'message', 'resendMail'),
                'error'   => $msg,
                'action'  => '?account=resend',
        )];
    }

    private function resend() : string
    {
        // no input yet. show clean form
        if (is_null($this->_post['email']))
            return '';

        // truncated due to validation fail
        if (!$this->_post['email'])
            return Lang::account('emailInvalid');

        $timeout = DB::Aowow()->selectCell('SELECT `unbanDate` FROM ::account_bannedips WHERE `ip` = %s AND `type` = %i AND `count` > %i AND `unbanDate` > UNIX_TIMESTAMP()', User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_COUNT'));

        // on cooldown pretend we dont know the email address
        if ($timeout && $timeout > time())
            return Cfg::get('DEBUG') ? 'resend on cooldown: '.DateTime::formatTimeElapsed($timeout * 1000).' remaining' : Lang::account('inputbox', 'error', 'emailNotFound');

        $rotation = AccountActivation::resend($this->_post['email'], User::$ip);
        if ($rotation['status'] === AccountActivation::BLOCKED)
            return Lang::account('inputbox', 'error', 'emailNotFound');
        if ($rotation['status'] === AccountActivation::FAILED)
            return Lang::main('intError');

        if ($rotation['status'] === AccountActivation::OK)
        {
            if (!Util::sendMail($this->_post['email'], 'activate-account', [$rotation['token']], Cfg::get('ACC_CREATE_SAVE_DECAY')))
                return Lang::main('intError');

            $this->success = true;
            return Lang::account('inputbox', 'message', 'createAccSent', [$this->_post['email']]);
        }

        // pretend recovery started
        // do not confirm or deny existence of email
        $this->success = !Cfg::get('DEBUG');
        return Cfg::get('DEBUG') ? Lang::account('inputbox', 'error', 'emailNotFound') : Lang::account('inputbox', 'message', 'createAccSent', [$this->_post['email']]);
    }
}

?>
