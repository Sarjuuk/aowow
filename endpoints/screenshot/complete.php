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

    /** Commit one owner/session-bound submission with its JPEG; consume staging only after acknowledged commit. */
    private function handleComplete() : bool
    {
        if (!User::canUploadScreenshot() || !$this->assertPOST('coords') ||
            !($stage = PrivateUpload::screenshotStage($this->imgHash, $this->destType, $this->destTypeId)))
            return false;

        $db = DB::Aowow();
        $started = $commitAttempted = $committed = false;
        $pending = null;

        try
        {
            if (!ScreenshotMgr::init() || !ScreenshotMgr::loadFile('%s', $stage['original']) ||
                !ScreenshotMgr::cropImg(...$this->_post['coords']))
                return false;

            ['oWidth' => $w, 'oHeight' => $h] = ScreenshotMgr::calcImgDimensions();
            if (!is_int($db->qry('START TRANSACTION')))
                return false;
            $started = true;

            // The unique claim serializes replay even if a moderator permanently deletes the screenshot row.
            if (!is_int($db->qry('INSERT INTO ::screenshot_uploads (`uploadKey`, `userIdOwner`, `expires`) VALUES (%s, %i, %i)',
                hash('sha256', User::$id.':'.$this->imgHash), User::$id, $stage['expires'])))
                return false;
            if (!PrivateUpload::screenshotStage($this->imgHash, $this->destType, $this->destTypeId))
                return false;

            $newId = $db->qry(
                'INSERT INTO ::screenshots (`type`, `typeId`, `userIdOwner`, `date`, `width`, `height`, `caption`, `status`) VALUES (%i, %i, %i, UNIX_TIMESTAMP(), %i, %i, %s, 0)',
                $this->destType, $this->destTypeId, User::$id, $w, $h,
                $this->handleCaption($this->_post['screenshotalt'])
            );
            if (!is_int($newId) || $newId <= 0)
                return false;
            $pending = sprintf(ScreenshotMgr::PATH_PENDING, $newId);

            // Keep the row uncommitted until its JPEG exists; failed writes leave the upload retryable.
            if (!ScreenshotMgr::writeImage(ScreenshotMgr::PATH_PENDING, $newId))
                return false;

            $commitAttempted = true;
            if (!is_int($db->qry('COMMIT')))
                return false;
            $committed = true;
            PrivateUpload::consumeScreenshot($this->imgHash, $stage);
            // Bounded reclamation runs after commit, outside the unique-claim transaction's locks.
            $db->qry('DELETE FROM ::screenshot_uploads WHERE `expires` <= %i ORDER BY `expires` LIMIT 100', time());
            return true;
        }
        catch (\Throwable)
        {
            return $committed;
        }
        finally
        {
            if ($started && !$committed)
            {
                $db->qry('ROLLBACK');
                // A failed COMMIT response may have committed: never remove that potentially live pending image.
                if (!$commitAttempted && $pending && is_file($pending))
                    unlink($pending);
            }
        }
    }

    protected static function checkCoords(string $val) : ?array
    {
        if (preg_match('/^[01]\.[0-9]{3}(,[01]\.[0-9]{3}){3}$/D', $val))
        {
            $coords = array_map('floatval', explode(',', $val));
            [$x, $y, $w, $h] = $coords;
            // The cropper independently rounds origin/size to three decimals; tolerate that edge then clamp.
            if ($x < 1 && $y < 1 && $w > 0 && $w <= 1 && $h > 0 && $h <= 1 &&
                $x + $w <= 1.001001 && $y + $h <= 1.001001)
                return [$x, $y, min($w, 1 - $x), min($h, 1 - $y)];
        }

        return null;
    }
}

?>
