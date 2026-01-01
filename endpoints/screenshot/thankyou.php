<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
    2. =crop: user edites upload
    3. =complete: store edited screenshot file and data
->  4. =thankyou
*/

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotThankyouResponse extends TemplateResponse
{
    protected bool   $requiresLogin = true;

    protected string $template      = 'text-page-generic';
    protected string $pageName      = 'screenshot';

    private int $destType   = 0;
    private int $destTypeId = 0;

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generateError();

        [, $this->destType, $this->destTypeId, , $imgHash] = $m;

        // no such type or this type cannot receive screenshots
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_SS))
            $this->generateError();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generateError();

        // only accept/expect hash for crop & complete
        if ($imgHash)
            $this->generateError();
    }

    protected function generate() : void
    {
        $this->h1 = Lang::screenshot('submission');

        array_unshift($this->title, $this->h1);

        $this->extraHTML  = Lang::screenshot('thanks', 'contrib').'<br /><br />';
        $this->extraHTML .= Lang::screenshot('thanks', 'goBack', [Type::getFileString($this->destType), $this->destTypeId])."<br /><br />\n";
        $this->extraHTML .= '<i>'.Lang::screenshot('thanks', 'note').'</i>';

        parent::generate();
    }
}

?>
