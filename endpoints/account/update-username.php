<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings form submit
 * write status to session and redirect to account settings
 */

class AccountUpdateusernameResponse extends TextResponse
{
    protected ?string $redirectTo    = '?account#personal';
    protected  bool   $requiresLogin = true;

    protected  array  $expectedPOST  = array(
        'newUsername' => ['filter' => FILTER_CALLBACK, 'options' => [Util::class, 'validateUsername']]
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

        if ($msg = $this->updateUsername())
            $_SESSION['msg'] = ['username', $this->success, $msg];
    }

    private function updateUsername() : string
    {
        if (!$this->assertPOST('newUsername'))
            return Lang::main('intError');

        if (DB::Aowow()->selectCell('SELECT `renameCooldown` FROM ?_account WHERE `id` = ?d', User::$id) > time())
            return Lang::main('intError');                  // should have grabbed the error response..

        // yes, including your current name. you don't want to change into your current name, right?
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_account WHERE LOWER(`username`) = LOWER(?)', $this->_post['newUsername']))
            return Lang::account('nameInUse');

        DB::Aowow()->query('UPDATE ?_account SET `username` = ?, `renameCooldown` = ?d WHERE `id` = ?d', $this->_post['newUsername'], time() + Cfg::get('acc_rename_decay'), User::$id);

        $this->success = true;
        return Lang::account('updateMessage', 'username', [User::$username, $this->_post['newUsername']]);
    }
}

?>
