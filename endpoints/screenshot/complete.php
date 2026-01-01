<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
    2. =crop: user edites upload
->  3. =complete: store edited screenshot file and data
    4. =thankyou
*/

// filename: Username-type-typeId-<hash>[_original].jpg

class ScreenshotCompleteResponse extends TextResponse
{
    use TrCommunityHelper;

    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'coords'        => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkCoords']  ],
        'screenshotalt' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    private int    $destType    = 0;
    private int    $destTypeId  = 0;
    private string $imgHash     = '';

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get screenshot destination
        // target delivered as screenshot=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^screenshot=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generate404();

        [, $this->destType, $this->destTypeId, , $this->imgHash] = $m;

        // no such type or this type cannot receive screenshots
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_SS))
            $this->generate404();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generate404();

        //  hash required for crop & complete
        if (!$this->imgHash)
            $this->generate404();
    }

    protected function generate() : void
    {
        if ($this->handleComplete())
            $this->forward('?screenshot=thankyou&'.$this->destType.'.'.$this->destTypeId);
        else
            $this->generate404();
    }

    private function handleComplete() : bool
    {
        if (!$this->assertPOST('coords'))
            return false;

        ScreenshotMgr::init();

        if (!ScreenshotMgr::loadFile(ScreenshotMgr::PATH_TEMP, User::$username.'-'.$this->destType.'-'.$this->destTypeId.'-'.$this->imgHash.'_original'))
            return false;

        ScreenshotMgr::cropImg(...$this->_post['coords']);

        ['oWidth' => $w, 'oHeight' => $h] = ScreenshotMgr::calcImgDimensions();

        // write to db
        $newId = DB::Aowow()->query(
           'INSERT INTO ?_screenshots (`type`, `typeId`, `userIdOwner`, `date`, `width`, `height`, `caption`, `status`) VALUES (?d, ?d, ?d, UNIX_TIMESTAMP(), ?d, ?d, ?, 0)',
            $this->destType, $this->destTypeId,
            User::$id,
            $w, $h,
            $this->handleCaption($this->_post['screenshotalt'])
        );
        if (!is_int($newId))                                // 0 is valid, NULL or FALSE is not
        {
            trigger_error('ScreenshotCompleteResponse - screenshot query failed', E_USER_ERROR);
            return false;
        }

        // write to file
        return ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, $newId);
    }

    protected static function checkCoords(string $val) : ?array
    {
        if (preg_match('/^[01]\.[0-9]{3}(,[01]\.[0-9]{3}){3}$/', $val))
            return explode(',', $val);

        return null;
    }
}

?>
