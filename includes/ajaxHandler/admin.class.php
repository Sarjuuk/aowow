<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

class AjaxAdmin extends AjaxHandler
{
    protected $validParams = ['screenshots', 'siteconfig'];
    protected $_get        = array(
        'action' => [FILTER_SANITIZE_STRING,     0xC],          // FILTER_FLAG_STRIP_LOW | *_HIGH
        'id'     => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkId']],
        'all'    => [FILTER_UNSAFE_RAW,          null],
        'type'   => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'typeid' => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'user'   => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkUser']],
        'val'    => [FILTER_UNSAFE_RAW,          null]
    );
    protected $_post       = array(
        'alt' => [FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW],
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        // requires 'action' parameter in any case
        if (!$this->_get['action'] || !$this->params)
            return;

        if ($this->params[0] == 'screenshots')
        {
            if (!User::isInGroup(U_GROUP_STAFF | U_GROUP_SCREENSHOT))  // comment_mod, handleSSmod, vi_mod ?
                return;

            if ($this->_get['action'] == 'list')
                $this->handler = 'ssList';
            else if ($this->_get['action'] == 'manage')
                $this->handler = 'ssManage';
            else if ($this->_get['action'] == 'editalt')
                $this->handler = 'ssEditAlt';
            else if ($this->_get['action'] == 'approve')
                $this->handler = 'ssApprove';
            else if ($this->_get['action'] == 'sticky')
                $this->handler = 'ssSticky';
            else if ($this->_get['action'] == 'delete')
                $this->handler = 'ssDelete';
            else if ($this->_get['action'] == 'relocate')
                $this->handler = 'ssRelocate';
        }
        else if ($this->params[0] == 'siteconfig')
        {
            if (!User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
                return;

            if ($this->_get['action'] == 'add')
                $this->handler = 'confAdd';
            else if ($this->_get['action'] == 'remove')
                $this->handler = 'confRemove';
            else if ($this->_get['action'] == 'update')
                $this->handler = 'confUpdate';
        }
    }

    // get all => null (optional)
    // evaled response .. UNK
    protected function ssList()
    {
        // ssm_screenshotPages
        // ssm_numPagesFound

        $pages = CommunityContent::getScreenshotPagesForManager($this->_get['all'], $nPages);
        $buff  = 'ssm_screenshotPages = '.Util::toJSON($pages).";\n";
        $buff .= 'ssm_numPagesFound = '.$nPages.';';

        return $buff;
    }

    // get: [type => type, typeId => typeId] || [user => username]
    // evaled response .. UNK
    protected function ssManage()
    {
        $res = [];

        if ($this->_get['type'] && $this->_get['type'] && $this->_get['typeid'] && $this->_get['typeid'])
            $res = CommunityContent::getScreenshotsForManager($this->_get['type'], $this->_get['typeid']);
        else if ($this->_get['user'])
            if ($uId = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE displayName = ?', $this->_get['user']))
                $res = CommunityContent::getScreenshotsForManager(0, 0, $uId);

        return 'ssm_screenshotData = '.Util::toJSON($res);
    }

    // get: id => SSid
    // resp: ''
    protected function ssEditAlt()
    {
        // doesn't need to be htmlEscaped, ths javascript does that
        if ($this->_get['id'] && $this->_post['alt'] !== null)
            DB::Aowow()->query('UPDATE ?_screenshots SET caption = ? WHERE id = ?d', trim($this->_post['alt']), $this->_get['id'][0]);

        return '';
    }

    // get: id => comma-separated SSids
    // resp: ''
    protected function ssApprove()
    {
        if (!$this->_get['id'])
            return '';

        // create resized and thumb version of screenshot
        $resized = [772, 618];
        $thumb   = [150, 150];
        $path    = 'static/uploads/screenshots/%s/%d.jpg';

        foreach ($this->_get['id'] as $id)
        {
            // must not be already approved
            if ($_ = DB::Aowow()->selectRow('SELECT userIdOwner, date FROM ?_screenshots WHERE (status & ?d) = 0 AND id = ?d', CC_FLAG_APPROVED, $id))
            {
                // should also error-log
                if (!file_exists(sprintf($path, 'pending', $id)))
                    continue;

                $srcImg = imagecreatefromjpeg(sprintf($path, 'pending', $id));
                $srcW   = imagesx($srcImg);
                $srcH   = imagesy($srcImg);

                // write thumb
                $scale   = min(1.0, min($thumb[0] / $srcW, $thumb[1] / $srcH));
                $destW   = $srcW * $scale;
                $destH   = $srcH * $scale;
                $destImg = imagecreatetruecolor($destW, $destH);

                imagefill($destImg, 0, 0, imagecolorallocate($destImg, 255, 255, 255));
                imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $destW, $destH, $srcW, $srcH);

                imagejpeg($destImg, sprintf($path, 'thumb', $id), 100);

                // write resized (only if required)
                if ($srcW > $resized[0] || $srcH > $resized[1])
                {
                    $scale   = min(1.0, min($resized[0] / $srcW, $resized[1] / $srcH));
                    $destW   = $srcW * $scale;
                    $destH   = $srcH * $scale;
                    $destImg = imagecreatetruecolor($destW, $destH);

                    imagefill($destImg, 0, 0, imagecolorallocate($destImg, 255, 255, 255));
                    imagecopyresampled($destImg, $srcImg, 0, 0, 0, 0, $destW, $destH, $srcW, $srcH);

                    imagejpeg($destImg, sprintf($path, 'resized', $id), 100);
                }

                imagedestroy($srcImg);

                // move screenshot from pending to normal
                rename(sprintf($path, 'pending', $id), sprintf($path, 'normal', $id));

                // set as approved in DB and gain rep (once!)
                DB::Aowow()->query('UPDATE ?_screenshots SET status = ?d, userIdApprove = ?d WHERE id = ?d', CC_FLAG_APPROVED, User::$id, $id);
                Util::gainSiteReputation($_['userIdOwner'], SITEREP_ACTION_UPLOAD, ['id' => $id, 'what' => 1, 'date' => $_['date']]);
            }
        }

        return '';
    }

    // get: id => comma-separated SSids
    // resp: ''
    protected function ssSticky()
    {
        if (!$this->_get['id'])
            return '';

        // approve soon to be sticky screenshots
        $this->ssApprove();

        // this one is a bit strange: as far as i've seen, the only thing a 'sticky' screenshot does is show up in the infobox
        // this also means, that only one screenshot per page should be sticky
        // so, handle it one by one and the last one affecting one particular type/typId-key gets the cake
        foreach ($this->_get['id'] as $id)
        {
            // reset all others
            DB::Aowow()->query('UPDATE ?_screenshots a, ?_screenshots b SET a.status = a.status & ~?d WHERE a.type = b.type AND a.typeId = b.typeId AND a.id <> b.id AND b.id = ?d', CC_FLAG_STICKY, $id);

            // toggle sticky status
            DB::Aowow()->query('UPDATE ?_screenshots SET `status` = IF(`status` & ?d, `status` & ~?d, `status` | ?d) WHERE id = ?d AND `status` & ?d', CC_FLAG_STICKY, CC_FLAG_STICKY, CC_FLAG_STICKY, $id, CC_FLAG_APPROVED);
        }

        return '';
    }

    // get: id => comma-separated SSids
    // resp: ''
    // 2 steps: 1) remove from sight, 2) remove from disk
    protected function ssDelete()
    {
        if (!$this->_get['id'])
            return '';

        $path = 'static/uploads/screenshots/%s/%d.jpg';

        foreach ($this->_get['id'] as $id)
        {
            // irrevocably remove already deleted files
            if (DB::Aowow()->selectCell('SELECT 1 FROM ?_screenshots WHERE status & ?d AND id = ?d', CC_FLAG_DELETED, $id))
            {
                DB::Aowow()->query('DELETE FROM ?_screenshots WHERE id = ?d', $id);
                if (file_exists(sprintf($path, 'pending', $id)))
                    unlink(sprintf($path, 'pending', $id));

                continue;
            }

            // move pending or normal to pending
            if (file_exists(sprintf($path, 'normal', $id)))
                rename(sprintf($path, 'normal', $id), sprintf($path, 'pending', $id));

            // remove resized and thumb
            if (file_exists(sprintf($path, 'thumb', $id)))
                unlink(sprintf($path, 'thumb', $id));

            if (file_exists(sprintf($path, 'resized', $id)))
                unlink(sprintf($path, 'resized', $id));
        }

        // flag as deleted if not aready
        DB::Aowow()->query('UPDATE ?_screenshots SET status = ?d, userIdDelete = ?d WHERE id IN (?a)', CC_FLAG_DELETED, User::$id, $ids);

        return '';
    }

    // get: id => ssId, typeid => typeId    (but not type..?)
    // resp: ''
    protected function ssRelocate()
    {
        if (!$this->_get['id'] || !$this->_get['typeid'])
            return '';

        $type   = DB::Aowow()->selectCell('SELECT type FROM ?_screenshots WHERE id = ?d', $this->_get['id']);
        $typeId = (int)$this->_get['typeid'];

        if (!(new Util::$typeClasses[$type]([['id', $typeId]]))->error)
            DB::Aowow()->query('UPDATE ?_screenshots SET typeId = ?d WHERE id = ?d', $typeId, $this->_get['id'][0]);

        return '';
    }

    protected function confAdd()
    {
        $key = $this->_get['id'];
        $val = $this->_get['val'];

        if ($key === null)
            return 'empty option name given';

        if (!strlen($key))
            return 'invalid chars in option name: [a-z 0-9 _ . -] are allowed';

        if (ini_get($key) === false || ini_set($key, $val) === false)
            return 'this configuration option cannot be set';

        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_config WHERE `flags` & ?d AND `key` = ?', CON_FLAG_PHP, $key))
            return 'this configuration option is already in use';

        DB::Aowow()->query('INSERT IGNORE INTO ?_config (`key`, `value`, `flags`) VALUES (?, ?, ?d)', $key, $val, CON_FLAG_TYPE_STRING | CON_FLAG_PHP);
        return '';
    }

    protected function confRemove()
    {
        if (!$this->_get['id'])
            return 'invalid configuration option given';

        if (DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0', $this->_get['id'], CON_FLAG_PERSISTENT))
            return '';
        else
            return 'option name is either protected or was not found';
    }

    protected function confUpdate()
    {
        $key = trim($this->_get['id']);
        $val = trim($this->_get['val']);

        if (!strlen($key))
            return 'empty option name given';

        $flags = DB::Aowow()->selectCell('SELECT `flags` FROM ?_config WHERE `key` = ?', $key);
        if (!$flags)
            return 'configuration option not found';

        if (!($flags & CON_FLAG_TYPE_STRING) && !strlen($val))
            return 'empty value given';
        else if ($flags & CON_FLAG_TYPE_INT && !preg_match('/^-?\d+$/i', $val))
            return "value must be integer";
        else if ($flags & CON_FLAG_TYPE_FLOAT && !preg_match('/^-?\d*(,|.)?\d+$/i', $val))
            return "value must be float";
        else if ($flags & CON_FLAG_TYPE_BOOL)
            $val = (int)!!$val;                 // *snort* bwahahaa

        DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $val, $key);
        return '';
    }

    protected function checkId($val)
    {
        if (!$this->params)
            return null;

        // expecting id-list
        if ($this->params[0] == 'screenshots')
        {
            if (preg_match('/\d+(,\d+)*/', $val))
                return array_map('intVal', explode(',', $val));

            return null;
        }

        // expecting string
        if ($this->params[0] == 'siteconfig')
        {
            if (preg_match('/[^a-z0-9_\.\-]/i', $val))
                return '';

            return strtolower($val);
        }

        return null;
    }

    protected function checkUser($val)
    {
        $n = Util::lower(trim(urldecode($val)));

        if (User::isValidName($n))
            return $n;

        return null;
    }
}
