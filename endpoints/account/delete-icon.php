<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via form button on user settings page
 */

class AccountDeleteiconResponse extends TextResponse
{
    protected bool  $requiresLogin     = true;
    protected int   $requiredUserGroup = U_GROUP_PREMIUM_PERMISSIONS;

    protected array $expectedPOST      = array(
        'id' => ['filter' => FILTER_VALIDATE_INT]
    );

    /*
     * response not evaluated
     */
    protected function generate() : void
    {
        if (User::isBanned() || !$this->assertPOST('id'))
            return;

        // non-int > error
        $selected = DB::Aowow()->selectCell('SELECT `current` FROM ?_account_avatars WHERE `id` = ?d AND `userId` = ?d', $this->_post['id'], User::$id);
        if ($selected === null || $selected === false)
            return;

        DB::Aowow()->query('DELETE FROM ?_account_avatars WHERE `id` = ?d AND `userId` = ?d', $this->_post['id'], User::$id);

        // if deleted avatar is also currently selected, unset
        if ($selected)
            DB::Aowow()->query('UPDATE ?_account SET `avatar` = 0 WHERE `id` = ?d', User::$id);

        $path = sprintf('static/uploads/avatars/%d.jpg', $this->_post['id']);
        if (!unlink($path))
            trigger_error('AccountDeleteiconResponse - failed to delete file: '.$path, E_USER_ERROR);
    }
}

?>
