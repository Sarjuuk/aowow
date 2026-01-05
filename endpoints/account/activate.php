<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via activation email link
 * empty page with status box
 */

class AccountActivateResponse extends TemplateResponse
{
    protected string $template    = 'text-page-generic';
    protected string $pageName    = 'activate';

    protected array  $expectedGET = array(
        'key' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']]
    );

    private bool $success = false;

    public function __construct()
    {
        parent::__construct();

        if (!Cfg::get('ACC_ALLOW_REGISTER') || Cfg::get('ACC_AUTH_MODE') != AUTH_MODE_SELF)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->title[] = Lang::account('title');

        $msg = $this->activate();

        if ($this->success)
            $this->inputbox = ['inputbox-status', ['head' => Lang::account('inputbox', 'head', 'register', [2]), 'message' => $msg]];
        else
        {
            $_SESSION['error']['activate'] = $msg;
            $this->forward('?account=resend');
        }

        parent::generate();
    }

    private function activate() : string
    {
        if (!$this->assertGET('key'))
            return Lang::main('intError');

        if (DB::Aowow()->selectCell('SELECT `id` FROM ::account WHERE `status` IN %in AND `token` = %s', [ACC_STATUS_NONE, ACC_STATUS_NEW], $this->_get['key']))
        {
            // don't remove the token yet. It's needed on signin page.
            DB::Aowow()->qry('UPDATE ::account SET `status` = %i, `statusTimer` = 0, `userGroups` = %i WHERE `token` = %s', ACC_STATUS_NONE, U_GROUP_NONE, $this->_get['key']);

            // fully apply block for further registration attempts from this ip
            DB::Aowow()->qry('REPLACE INTO ::account_bannedips (`ip`, `type`, `count`, `unbanDate`) VALUES (%s, %i, %i + 1, UNIX_TIMESTAMP() + %i)',
                User::$ip, IP_BAN_TYPE_REGISTRATION_ATTEMPT, Cfg::get('ACC_FAILED_AUTH_COUNT'), Cfg::get('ACC_FAILED_AUTH_BLOCK'));

            $this->success = true;
            return Lang::account('inputbox', 'message', 'accActivated', [$this->_get['key']]);
        }

        // grace period expired and other user claimed name
        return Lang::main('intError');
    }
}

?>
