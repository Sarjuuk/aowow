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
        $selected = DB::Aowow()->selectCell('SELECT `current` FROM ::account_avatars WHERE `id` = %i AND `userId` = %i', $this->_post['id'], User::$id);
        if ($selected === null || $selected === false)
            return;

        DB::Aowow()->qry('DELETE FROM ::account_avatars WHERE `id` = %i AND `userId` = %i', $this->_post['id'], User::$id);

        // if deleted avatar is also currently selected, unset
        if ($selected)
            DB::Aowow()->qry('UPDATE ::account SET `avatar` = 0 WHERE `id` = %i', User::$id);

        $path = sprintf('static/uploads/avatars/%d.jpg', $this->_post['id']);
        if (!unlink($path))
            trigger_error('AccountDeleteiconResponse - failed to delete file: '.$path, E_USER_ERROR);
    }
}

?>
