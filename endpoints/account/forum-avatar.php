<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed via form submit on user settings page
 */

class AccountForumavatarResponse extends TextResponse
{
    protected ?string $redirectTo        = '?account#community';
    protected  bool   $requiresLogin     = true;

    // called via form submit
    protected  array  $expectedPOST      = array(
        'avatar'     => ['filter' => FILTER_VALIDATE_INT,    'options' => ['min_range' => 0, 'max_range' => 2 ]],
        'wowicon'    => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[[:print:]]+$/'     ]], // file name can have \W chars: inv_misc_fork&knife, achievement_dungeon_drak'tharon_heroic
        'customicon' => ['filter' => FILTER_VALIDATE_INT,    'options' => ['min_range' => 1                   ]]
    );
    // called via ajax
    protected  array  $expectedGET       = array(
        'avatar'     => ['filter' => FILTER_VALIDATE_INT,    'options' => ['min_range' => 2, 'max_range' => 2]],
        'customicon' => ['filter' => FILTER_VALIDATE_INT,    'options' => ['min_range' => 1                  ]]
    );

    private bool $success = false;

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        $msg = match ($this->_post['avatar'] ?? $this->_get['avatar'])
        {
            0 => $this->unset(),                            // none
            1 => $this->fromIcon(),                         // wow icon
            2 => $this->fromUpload(!$this->_get['avatar']), // custom icon (premium feature)
            default => Lang::main('genericError')
        };

        if ($msg)
            $_SESSION['msg'] = ['avatar', $this->success, $msg];
    }

    private function unset() : string
    {
        $x = DB::Aowow()->query('UPDATE ?_account SET `avatar` = 0 WHERE `id` = ?d', User::$id);
        if ($x === null || $x === false)
            return Lang::main('genericError');

        $this->success = true;

        return Lang::account('updateMessage', $x === 0 ? 'avNoChange' : 'avSuccess');
    }

    private function fromIcon() : string
    {
        if (!$this->assertPOST('wowicon'))
            return Lang::main('intError');

        $icon = strtolower(trim($this->_post['wowicon']));

        if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_icons WHERE `name` = ?', $icon))
            return Lang::account('updateMessage', 'avNotFound');

        $x = DB::Aowow()->query('UPDATE ?_account SET `avatar` = 1, `wowicon` = ? WHERE `id` = ?d', strtolower($icon), User::$id);
        if ($x === null || $x === false)
            return Lang::main('genericError');

        $this->success = true;

        $msg = Lang::account('updateMessage', $x === 0 ? 'avNoChange' : 'avSuccess');
        if (($qty = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_account WHERE `wowicon` = ?', $icon)) > 1)
            $msg .= ' '.Lang::account('updateMessage', 'avNthUser', [$qty]);
        else
            $msg .= ' '.Lang::account('updateMessage', 'av1stUser');

        return $msg;
    }

    protected function fromUpload(bool $viaPOST) : string
    {
        if (!User::isPremium())
            return Lang::main('genericError');

        if (($viaPOST && !$this->assertPOST('customicon')) || (!$viaPOST && !$this->assertGET('customicon')))
            return Lang::main('intError');

        $customIcon = $this->_post['customicon'] ?? $this->_get['customicon'];

        $x = DB::Aowow()->query('UPDATE ?_account_avatars SET `current` = IF(`id` = ?d, 1, 0) WHERE `userId` = ?d AND `status` <> ?d', $customIcon, User::$id, AvatarMgr::STATUS_REJECTED);
        if (!is_int($x))
            return Lang::main('genericError');

        if (!is_int(DB::Aowow()->query('UPDATE ?_account SET `avatar` = 2 WHERE `id` = ?d', User::$id)))
            return Lang::main('intError');

        $this->success = true;

        return Lang::account('updateMessage', $x === 0 ? 'avNoChange' : 'avSuccess');
    }
}

?>
