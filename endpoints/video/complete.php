<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
    1. =add: receives user upload
    2. =crop: user edites upload
->  3. =complete: store edited video file and data
    4. =thankyou
*/

class VideoCompleteResponse extends TextResponse
{
    use TrCommunityHelper;

    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'caption' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
    );

    private string $videoHash  = '';
    private int    $destType   = 0;
    private int    $destTypeId = 0;

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        // get video destination
        // target delivered as video=<command>&<type>.<typeId>.<hash:16> (hash is optional)
        if (!preg_match('/^video=\w+&(-?\d+)\.(-?\d+)(\.(\w{16}))?$/i', $_SERVER['QUERY_STRING'] ?? '', $m, PREG_UNMATCHED_AS_NULL))
            $this->generate404();

        [, $this->destType, $this->destTypeId, , $this->videoHash] = $m;

        // no such type or this type cannot receive videos
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_VI))
            $this->generate404();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generate404();
    }

    protected function generate() : void
    {
        if ($this->handleComplete())
            $this->forward('?video=thankyou&'.$this->destType.'.'.$this->destTypeId);
        else
            $this->generate404();
    }

    private function handleComplete() : bool
    {
        if (!VideoMgr::loadSuggestion($videoInfo, $this->destType, $this->destTypeId, $this->videoHash))
            $this->generate404();

        $pos = DB::Aowow()->selectCell('SELECT MAX(`pos`) FROM ::videos WHERE `type` = %i AND `typeId` = %i AND (`status` & %i) = 0', $this->destType, $this->destTypeId, CC_FLAG_DELETED);
        if (!is_int($pos))
            $pos = -1;

        // write to db
        $newId = DB::Aowow()->qry(
           'INSERT INTO ::videos (`type`, `typeId`, `userIdOwner`, `date`, `videoId`, `pos`, `url`, `width`, `height`, `name`, `caption`, `status`) VALUES (%i, %i, %i, UNIX_TIMESTAMP(), %s, %i, %s, %i, %i, %s, %s, 0)',
            $this->destType, $this->destTypeId, User::$id,
            $videoInfo->id,
            $pos + 1,
            $videoInfo->thumbnail_url,
            $videoInfo->thumbnail_width,
            $videoInfo->thumbnail_height,
            $videoInfo->title,
            $this->handleCaption($this->_post['caption'])
        );

        if (!is_int($newId))                                // 0 is valid, NULL or FALSE is not
        {
            trigger_error('VideoCompleteResponse - video query failed', E_USER_ERROR);
            return false;
        }

        VideoMgr::dropTempFile();

        return true;
    }
}

?>
