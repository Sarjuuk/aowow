<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
->  2. =crop: user edites upload
    2.1. just show edit page
    2.2. user submits coords and description to =complete
    3. =complete: store edited video file and data
    4. =thankyou
*/

class VideoConfirmResponse extends TemplateResponse
{
    protected bool   $requiresLogin = true;

    protected string $template      = 'video';
    protected string $pageName      = 'video';

    public ?Markup $infobox    = null;
    public  string $videoHash  = '';
    public  int    $destType   = 0;
    public  int    $destTypeId = 0;
    public  string $url        = '';
    public  int    $width      = 0;
    public  int    $height     = 0;
    public  array  $video      = [];
    public  string $viTitle    = '';

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get video destination
        // target delivered as video=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^video=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generateError();

        [, $this->destType, $this->destTypeId, , $this->videoHash] = $m;

        // no such type or this type cannot receive videos
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_VI))
            $this->generateError();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::video('submission');
        array_unshift($this->title, $this->h1);

        if (!VideoMgr::loadSuggestion($videoInfo, $this->destType, $this->destTypeId, $this->videoHash))
            $this->generateError();

        $this->viTitle = $videoInfo->title;
        $this->url     = $videoInfo->thumbnail_url;
        $this->width   = $videoInfo->thumbnail_width;
        $this->height  = $videoInfo->thumbnail_height;
        $this->video   = [[
            'videoType' => VideoMgr::TYPE_YOUTUBE,
            'videoId'   => $videoInfo->id,
            'caption'   => $videoInfo->title
        ]];

        // target
        $this->infobox = new Markup(Lang::screenshot('displayOn', [Lang::typeName($this->destType), Type::getFileString($this->destType), $this->destTypeId]), ['allow' => Markup::CLASS_STAFF, 'dbpage' => true], 'infobox-contents0');
        $this->extendGlobalIds($this->destType, $this->destTypeId);

        parent::generate();
    }
}

?>
