<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UploadImagecropResponse extends TemplateResponse
{
    protected bool   $requiresLogin     = true;
    protected int    $requiredUserGroup = U_GROUP_PREMIUM_PERMISSIONS;

    protected string $template          = 'image-crop';
    protected string $pageName          = 'image-crop';

    protected array  $scripts           = array(
        [SC_JS_FILE,  'js/Cropper.js'],
        [SC_CSS_FILE, 'css/Cropper.css']
    );

    public array  $cropper    = [];
    public int    $nextId     = 0;
    public string $imgHash    = '';

    public function __construct(string $rawParam)
    {
        if (User::isBanned())
            $this->generateError();

        parent::__construct($rawParam);
    }

    protected function generate() : void
    {
        if ($err = $this->handleUpload())
        {
            $_SESSION['msg'] = ['avatar', false, $err];
            $this->forward('?account#community');
        }

        $this->h1 = Lang::account('avatarSubmit');

        $fileBase   = User::$username.'-avatar-'.$this->nextId.'-'.$this->imgHash;
        $dimensions = AvatarMgr::calcImgDimensions();

        $this->cropper = $dimensions + array(
            'url'        => Cfg::get('STATIC_URL').'/uploads/temp/'.$fileBase.'.jpg',
            'parent'     => 'av-container',
            'minCrop'    => ICON_SIZE_LARGE,                // optional; defaults to 150 - min selection size (a square)
            'type'       => Type::NPC,                      // NPC: 15384 [OLDWorld Trigger (DO NOT DELETE)]
            'typeId'     => 15384,                          // = arbitrary image upload
            'constraint' => [1, 1]                          // [xMult, yMult] - relative size to each other (here: be square)
        );

        parent::generate();
    }

    private function handleUpload() : string
    {
        if (!AvatarMgr::init())
            return Lang::main('intError');

        if (!AvatarMgr::validateUpload())
            return AvatarMgr::$error;

        if (!AvatarMgr::loadUpload())
            return Lang::main('intError');

        $n = DB::Aowow()->selectCell('SELECT COUNT(1) FROM ?_account_avatars WHERE `userId` = ?d', User::$id);
        if ($n && $n > Cfg::get('ACC_MAX_AVATAR_UPLOADS'))
            return Lang::main('intError');

        // why is ++(<IntExpression>); illegal syntax? WHO KNOWS!?
        $this->nextId = (DB::Aowow()->selectCell('SELECT MAX(`id`) FROM ?_account_avatars') ?: 0) + 1;

        if (!AvatarMgr::tempSaveUpload(['avatar', $this->nextId], $this->imgHash))
            return Lang::main('intError');

        return '';
    }
}

?>
