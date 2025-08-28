<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via form button on user settings page
 */

class AccountRenameiconResponse extends TextResponse
{
    protected bool  $requiresLogin     = true;
    protected int   $requiredUserGroup = U_GROUP_PREMIUM_PERMISSIONS;

    protected array $expectedPOST      = array(
        'id'   => ['filter' => FILTER_VALIDATE_INT                                                               ],
        'name' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' =>'/^[a-zA-Z][a-zA-Z0-9 ]{0,19}$/']]
    );

    /*
     * response not evaluated
     */
    protected function generate() : void
    {
        if (User::isBanned() || !$this->assertPOST('id', 'name'))
            return;

        // regexp same as in account.js
        DB::Aowow()->query('UPDATE ?_account_avatars SET `name` = ? WHERE `id` = ?d AND `userId` = ?d', trim($this->_post['name']), $this->_post['id'], User::$id);
    }
}

?>
