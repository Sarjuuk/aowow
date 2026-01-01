<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
->  2. =crop: user edites upload
    2.1. just show edit page
    2.2. user submits coords and description to =complete
    3. =complete: store edited screenshot file and data
    4. =thankyou
*/

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotCropResponse extends TemplateResponse
{
    protected bool   $requiresLogin = true;

    protected string $template      = 'screenshot';
    protected string $pageName      = 'screenshot';

    protected array  $scripts       = [[SC_JS_FILE, 'js/Cropper.js'], [SC_CSS_FILE, 'css/Cropper.css']];

    public ?Markup  $infobox    = null;
    public  array   $cropper    = [];
    public  int     $destType   = 0;
    public  int     $destTypeId = 0;
    public  string  $imgHash    = '';

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generateError();

        [, $this->destType, $this->destTypeId, , $this->imgHash] = $m;

        // no such type or this type cannot receive screenshots
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_SS))
            $this->generateError();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generateError();

        //  hash required for crop & complete
        if (!$this->imgHash)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::screenshot('submission');
        $fileBase = User::$username.'-'.$this->destType.'-'.$this->destTypeId.'-'.$this->imgHash;

        array_unshift($this->title, $this->h1);

        ScreenshotMgr::init();

        if (!ScreenshotMgr::loadFile(ScreenshotMgr::PATH_TEMP, $fileBase.'_original'))
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            $this->forward('?'.Type::getFileString($this->destType).'='.$this->destTypeId.'#submit-a-screenshot');
        }

        $dims = ScreenshotMgr::calcImgDimensions();

        $this->cropper = $dims + array(
            'url'     => Cfg::get('STATIC_URL').'/uploads/screenshots/temp/'.$fileBase.'.jpg',
            'parent'  => 'ss-container',
            'minCrop' => ScreenshotMgr::$MIN_SIZE,          // optional; defaults to 150 - min selection size (a square)
            'type'    => $this->destType,                   // only used to check against NPC: 15384 [OLDWorld Trigger (DO NOT DELETE)] for U_GROUP_MODERATOR | U_GROUP_EDITOR. If successful drops minCrop constraint
            'typeId'  => $this->destTypeId                  // i guess this was used to upload arbitrary imagery for articles, blog posts, etc
        );

        // target
        $this->infobox = new Markup(Lang::screenshot('displayOn', [Lang::typeName($this->destType), Type::getFileString($this->destType), $this->destTypeId]), ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');
        $this->extendGlobalIds($this->destType, $this->destTypeId);

        parent::generate();
    }
}

?>
