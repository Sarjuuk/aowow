<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via form submit on user settings page
 */

class AccountPremiumborderResponse extends TextResponse
{
    protected ?string $redirectTo        = '?account#premium';
    protected  bool   $requiresLogin     = true;
    protected  int    $requiredUserGroup = U_GROUP_PREMIUM_PERMISSIONS;

    protected  array  $expectedPOST      = array(
        'avatarborder' => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 0, 'max_range' => 4]],
    );

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if (!$this->assertPOST('avatarborder'))
            return;

        $x = DB::Aowow()->qry('UPDATE ::account SET `avatarborder` = %i WHERE `id` = %i', $this->_post['avatarborder'], User::$id);
        if (is_null($x))
            $_SESSION['msg'] = ['premiumborder', false, Lang::main('genericError')];
        else if (!$x)
            $_SESSION['msg'] = ['premiumborder', true, Lang::account('updateMessage', 'avNoChange')];
        else
            $_SESSION['msg'] = ['premiumborder', true, Lang::account('updateMessage', 'avSuccess')];
    }
}

?>
