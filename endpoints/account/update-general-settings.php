<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via account settings form submit
 * write status to session and redirect to account settings
 */

class AccountUpdategeneralsettingsResponse extends TextResponse
{
    protected ?string $redirectTo    = '?account#general';
    protected  bool   $requiresLogin = true;

    protected  array  $expectedPOST  = array(
        'modelrace'   => ['filter' => FILTER_VALIDATE_INT, 'options' => ['default' => 0, 'min_range' => 1, 'max_range' => 11]],
        'modelgender' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['default' => 0, 'min_range' => 1, 'max_range' => 2] ],
        'idsInLists'  => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkCheckbox']                       ]
    );

    private bool $success = false;

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($message = $this->updateGeneral())
            $_SESSION['msg'] = ['general', $this->success, $message];
    }

    private function updateGeneral() : string
    {
        if (!$this->assertPOST('modelrace', 'modelgender'))
            return Lang::main('genericError');

        if ($this->_post['modelrace'] && !ChrRace::tryFrom($this->_post['modelrace']))
            return Lang::main('genericError');

        // js handles this as cookie, so saved as cookie; Q - also save in ?_account table?
        if (!DB::Aowow()->query('REPLACE INTO ?_account_cookies (`userId`, `name`, `data`) VALUES (?d, ?, ?)', User::$id, 'default_3dmodel', $this->_post['modelrace']. ',' . $this->_post['modelgender']))
            return Lang::main('genericError');

        if (!setcookie('default_3dmodel', $this->_post['modelrace']. ',' . $this->_post['modelgender'], 0, '/'))
            return Lang::main('intError');

        // int > number of edited rows > no changes is still success
        if (!is_int(DB::Aowow()->query('UPDATE ?_account SET `debug` = ?d WHERE `id` = ?d', $this->_post['idsInLists'] ? 1 : 0, User::$id)))
            return Lang::main('intError');

        $this->success = true;
        return Lang::account('updateMessage', 'general');
    }
}

?>
