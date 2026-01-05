<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
 * accessed from db detail pages, when clicking on the fav star near the h1 element
 * always returns emptry string
 */

class AccountFavoritesResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'add'        => ['filter' => FILTER_VALIDATE_INT],
        'remove'     => ['filter' => FILTER_VALIDATE_INT],
        'id'         => ['filter' => FILTER_VALIDATE_INT],
     // 'sessionKey' => ['filter' => FILTER_VALIDATE_REGEXP, 'options' => ['regexp' => '/^[a-zA-Z0-9]{40}$/']] // usage of sessionKey omitted
    );

    protected function generate() : void
    {
        if (User::isBanned())
            return;

        if ($this->_post['remove'])
            $this->removeFavorite();

        else if ($this->_post['add'])
            $this->addFavorite();
    }

    private function removeFavorite() : void
    {
        if ($this->assertPOST('id', 'remove'))
            DB::Aowow()->qry('DELETE FROM ::account_favorites WHERE `userId` = %i AND `type` = %i AND `typeId` = %i', User::$id, $this->_post['remove'], $this->_post['id']);
    }

    private function addFavorite() : void
    {
        if ($this->assertPOST('id', 'add') && Type::validateIds($this->_post['add'], $this->_post['id']))
            DB::Aowow()->qry('INSERT INTO ::account_favorites (`userId`, `type`, `typeId`) VALUES (%i, %i, %i)', User::$id, $this->_post['add'], $this->_post['id']);
        else
            trigger_error('AccountFavoritesResponse::addFavorite() - failed to add [userId: '.User::$id.', type: '.$this->_post['add'].', typeId: '.$this->_post['id'], E_USER_NOTICE);
    }
}

?>
