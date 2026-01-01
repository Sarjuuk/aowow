<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/*
->  1. =add: receives user upload
    1.1. checks and processing on the upload
    1.2. forward to =confirm or blank response
    2. =confirm: user edites upload
    3. =complete: store edited video file and data
    4. =thankyou
*/

class VideoAddResponse extends TextResponse
{
    protected bool  $requiresLogin = true;

    protected array $expectedPOST  = array(
        'videourl' => ['filter' => FILTER_CALLBACK, 'options' => [self::class, 'checkTextLine']]
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

        [, $this->destType, $this->destTypeId, , $videoHash] = $m;

        // no such type or this type cannot receive videos
        if (!Type::checkClassAttrib($this->destType, 'contribute', CONTRIBUTE_VI))
            $this->generate404();

        // no such typeId
        if (!Type::validateIds($this->destType, $this->destTypeId))
            $this->generate404();

        // only accept/expect hash for confirm & complete
        if ($videoHash)
            $this->generate404();
    }

    protected function generate() : void
    {
        if ($this->handleAdd())
            $this->redirectTo = '?video=confirm&'.$this->destType.'.'.$this->destTypeId.'.'.$this->videoHash;
        else if ($this->destType && $this->destTypeId)
            $this->redirectTo = '?'.Type::getFileString($this->destType).'='.$this->destTypeId.'#suggest-a-video';
        else
            $this->generate404();
    }

    private function handleAdd() : bool
    {
        if (!User::canSuggestVideo())
        {
            $_SESSION['error']['vi'] = Lang::video('error', 'notAllowed');
            return false;
        }

        if (!$this->assertPOST('videourl'))
        {
            $_SESSION['error']['vi'] = Lang::video('error', 'selectVI');
            return false;
        }

        $videoId = '';
        if (preg_match('/^https?:\/\/(www\.)?youtu(\.be|be\.com\/watch\?v=)([a-zA-Z0-9_-]{11})/', $this->_post['videourl'], $m))
            $videoId = $m[3];
        else
        {
            $_SESSION['error']['vi'] = Lang::video('error', 'selectVI');
            return false;
        }

        $curl = curl_init('https://youtube.com/oembed?format=json&url=https://www.youtube.com/watch?v='.$videoId);
        if (!$curl)
        {
            trigger_error('VideoAddResponse - curl_init fail', E_USER_ERROR);
            $_SESSION['error']['vi'] = Lang::main('intError');
            return false;
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $ytOembed = curl_exec($curl);
        $status   = curl_getinfo($curl, CURLINFO_RESPONSE_CODE);
        curl_close($curl);

        if ($status == 401)
        {
            $_SESSION['error']['vi'] = Lang::video('error', 'isPrivate');
            return false;
        }
        else if ($status != 200)                            // 404, 500 seen .. does it matter why its inaccessible?
        {
            $_SESSION['error']['vi'] = Lang::video('error', 'noExist');
            return false;
        }

        $videoInfo = json_decode($ytOembed);
        $videoInfo->id = $videoId;

        if (!VideoMgr::saveSuggestion($videoInfo, $this->destType, $this->destTypeId, $this->videoHash))
        {
            $_SESSION['error']['ss'] = Lang::main('intError');
            return false;
        }

        return true;
    }
}

?>
