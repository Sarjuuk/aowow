<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UploadImagecompleteResponse extends TextResponse
{
    protected  bool   $requiresLogin     = true;
    protected  int    $requiredUserGroup = U_GROUP_PREMIUM_PERMISSIONS;
    protected ?string $redirectTo        = '?account#community';

    protected array $expectedPOST  = array(
        'coords' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkCoords']],
    );

    public string $imgHash;
    public int    $newId;

    public function __construct(string $rawParam)
    {
        if (User::isBanned())
            $this->generate404();

        parent::__construct($rawParam);

        if (!preg_match('/^upload=image-complete&(\d+)\.(\w{16})$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generate404();

        [, $this->newId, $this->imgHash] = $m;

        if (!$this->imgHash || !$this->newId)
            $this->generate404();
    }

    protected function generate() : void
    {
        if (!$this->handleComplete())
            $_SESSION['msg'] = ['avatar', false, AvatarMgr::$error ?: Lang::main('intError')];
    }

    private function handleComplete() : bool
    {
        if (!$this->assertPOST('coords'))
            return false;

        if (!AvatarMgr::init())
            return false;

        if (!AvatarMgr::loadFile(AvatarMgr::PATH_TEMP, User::$username.'-avatar-'.$this->newId.'-'.$this->imgHash.'_original'))
            return false;

        if (!AvatarMgr::cropImg(...$this->_post['coords']))
            return false;

        if (!AvatarMgr::createAtlas($this->newId))
            return false;

        $fSize = filesize(sprintf(AvatarMgr::PATH_AVATARS, $this->newId));
        if (!$fSize)
            return false;

        $newId = DB::Aowow()->query('INSERT INTO ?_account_avatars (`id`, `userId`, `name`, `when`, `size`) VALUES (?d, ?d, ?, ?d, ?d)', $this->newId, User::$id, 'Avatar '.$this->newId, time(), $fSize);
        if (!is_int($newId))
        {
            trigger_error('UploadImagecompleteResponse - avatar query failed', E_USER_ERROR);
            return false;
        }

        // delete temp files
        unlink(sprintf(AvatarMgr::PATH_TEMP, User::$username.'-avatar-'.$this->newId.'-'.$this->imgHash.'_original'));
        unlink(sprintf(AvatarMgr::PATH_TEMP, User::$username.'-avatar-'.$this->newId.'-'.$this->imgHash));

        return true;
    }

    protected static function checkCoords(string $val) : ?array
    {
        if (preg_match('/^[01]\.[0-9]{3}(,[01]\.[0-9]{3}){3}$/', $val))
            return explode(',', $val);

        return null;
    }
}

?>
