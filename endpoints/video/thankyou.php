<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
    2. =crop: user edites upload
    3. =complete: store edited video file and data
->  4. =thankyou
*/

class VideoThankyouResponse extends TemplateResponse
{
    protected bool   $requiresLogin = true;

    protected string $template      = 'text-page-generic';
    protected string $pageName      = 'video';

    private int $destType   = 0;
    private int $destTypeId = 0;

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get video destination
        // target delivered as video=<command>&<type>.<typeId>
        if (!preg_match('/^video=\w+&(-?\d+)\.(-?\d+)$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generateError();

        [, $this->destType, $this->destTypeId] = $m;

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

        $this->extraHTML  = Lang::video('thanks', 'contrib').'<br /><br />';
        $this->extraHTML .= Lang::video('thanks', 'goBack', [Type::getFileString($this->destType), $this->destTypeId])."<br /><br />\n";
        $this->extraHTML .= '<i>'.Lang::video('thanks', 'note').'</i>';

        parent::generate();
    }
}

?>
