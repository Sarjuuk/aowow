<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxAdmin extends AjaxHandler
{
    protected $validParams = ['screenshots', 'siteconfig', 'weight-presets'];
    protected $_get        = array(
        'action' => [FILTER_SANITIZE_STRING,     0xC],          // FILTER_FLAG_STRIP_LOW | *_HIGH
        'id'     => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkId']],
        'key'    => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkKey']],
        'all'    => [FILTER_UNSAFE_RAW,          null],
        'type'   => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'typeid' => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'user'   => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkUser']],
        'val'    => [FILTER_UNSAFE_RAW,          null]
    );
    protected $_post       = array(
        'alt'    => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'id'     => [FILTER_SANITIZE_NUMBER_INT, null],
        'scale'  => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkScale']],
        '__icon' => [FILTER_CALLBACK,            ['options' => 'AjaxAdmin::checkKey']],
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        // requires 'action' parameter in any case
        if (!$this->_get['action'] || !$this->params)
            return;

        if ($this->params[0] == 'screenshots')
        {
            if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT))
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
        else if ($this->params[0] == 'weight-presets')
        {
            if (!User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_BUREAU))
                return;

            if ($this->_get['action'] == 'save')
                $this->handler = 'wtSave';
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
            if ($_ = DB::Aowow()->selectRow('SELECT userIdOwner, date, type, typeId FROM ?_screenshots WHERE (status & ?d) = 0 AND id = ?d', CC_FLAG_APPROVED, $id))
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
                // flag DB entry as having screenshots
                if (Util::$typeClasses[$_['type']] && ($tbl = get_class_vars(Util::$typeClasses[$_['type']])['dataTable']))
                    DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_SCREENSHOT, $_['typeId']);
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
            if (User::isInGroup(U_GROUP_ADMIN) && DB::Aowow()->selectCell('SELECT 1 FROM ?_screenshots WHERE status & ?d AND id = ?d', CC_FLAG_DELETED, $id))
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
        $oldEntries = DB::Aowow()->selectCol('SELECT `type` AS ARRAY_KEY, GROUP_CONCAT(typeId) FROM ?_screenshots WHERE id IN (?a) GROUP BY `type`', $this->_get['id']);
        DB::Aowow()->query('UPDATE ?_screenshots SET status = ?d, userIdDelete = ?d WHERE id IN (?a)', CC_FLAG_DELETED, User::$id, $this->_get['id']);
        // deflag db entry as having screenshots
        foreach ($oldEntries as $type => $typeIds)
        {
            $typeIds  = explode(',', $typeIds);
            $toUnflag = DB::Aowow()->selectCol('SELECT typeId AS ARRAY_KEY, IF(BIT_OR(`status`) & ?d, 1, 0) AS hasMore FROM ?_screenshots WHERE `type` = ?d AND typeId IN (?a) GROUP BY typeId HAVING hasMore = 0', CC_FLAG_APPROVED, $type, $typeIds);
            if ($toUnflag && Util::$typeClasses[$type] && ($tbl = get_class_vars(Util::$typeClasses[$type])['dataTable']))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags & ~?d WHERE id IN (?a)', CUSTOM_HAS_SCREENSHOT, array_keys($toUnflag));
        }

        return '';
    }

    // get: id => ssId, typeid => typeId    (but not type..?)
    // resp: ''
    protected function ssRelocate()
    {
        if (!$this->_get['id'] || !$this->_get['typeid'])
            return '';

        $id                     = $this->_get['id'][0];
        list($type, $oldTypeId) = array_values(DB::Aowow()->selectRow('SELECT type, typeId FROM ?_screenshots WHERE id = ?d', $id));
        $typeId                 = (int)$this->_get['typeid'];

        $tc = new Util::$typeClasses[$type]([['id', $typeId]]);
        if (!$tc->error)
        {
            // move screenshot
            DB::Aowow()->query('UPDATE ?_screenshots SET typeId = ?d WHERE id = ?d', $typeId, $id);

            // flag target as having screenshot
            DB::Aowow()->query('UPDATE '.$tc::$dataTable.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_SCREENSHOT, $typeId);

            // deflag source for having had screenshots (maybe)
            $ssInfo = DB::Aowow()->selectRow('SELECT IF(BIT_OR(~status) & ?d, 1, 0) AS hasMore FROM ?_screenshots WHERE `status`& ?d AND `type` = ?d AND typeId = ?d', CC_FLAG_DELETED, CC_FLAG_APPROVED, $type, $oldTypeId);
            if($ssInfo || !$ssInfo['hasMore'])
                DB::Aowow()->query('UPDATE '.$tc::$dataTable.' SET cuFlags = cuFlags & ~?d WHERE id = ?d', CUSTOM_HAS_SCREENSHOT, $oldTypeId);
        }

        return '';
    }

    protected function confAdd()
    {
        $key = trim($this->_get['key']);
        $val = trim(urldecode($this->_get['val']));

        if ($key === null)
            return 'empty option name given';

        if (!strlen($key))
            return 'invalid chars in option name: [a-z 0-9 _ . -] are allowed';

        if (ini_get($key) === false || ini_set($key, $val) === false)
            return 'this configuration option cannot be set';

        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_config WHERE `flags` & ?d AND `key` = ?', CON_FLAG_PHP, $key))
            return 'this configuration option is already in use';

        DB::Aowow()->query('INSERT IGNORE INTO ?_config (`key`, `value`, `cat`, `flags`) VALUES (?, ?, 0, ?d)', $key, $val, CON_FLAG_TYPE_STRING | CON_FLAG_PHP);
        return '';
    }

    protected function confRemove()
    {
        if (!$this->_get['key'])
            return 'invalid configuration option given';

        if (DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0', $this->_get['key'], CON_FLAG_PERSISTENT))
            return '';
        else
            return 'option name is either protected or was not found';
    }

    protected function confUpdate()
    {
        $key = trim($this->_get['key']);
        $val = trim(urldecode($this->_get['val']));
        $msg = '';

        if (!strlen($key))
            return 'empty option name given';

        $cfg = DB::Aowow()->selectRow('SELECT `flags`, `value` FROM ?_config WHERE `key` = ?', $key);
        if (!$cfg)
            return 'configuration option not found';

        if (!($cfg['flags'] & CON_FLAG_TYPE_STRING) && !strlen($val))
            return 'empty value given';
        else if ($cfg['flags'] & CON_FLAG_TYPE_INT && !preg_match('/^-?\d+$/i', $val))
            return "value must be integer";
        else if ($cfg['flags'] & CON_FLAG_TYPE_FLOAT && !preg_match('/^-?\d*(,|.)?\d+$/i', $val))
            return "value must be float";
        else if ($cfg['flags'] & CON_FLAG_TYPE_BOOL)
            $val = (int)!!$val;                 // *snort* bwahahaa

        DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $val, $key);
        if (!$this->confOnChange($key, $val, $msg))
            DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $cfg['value'], $key);

        return $msg;
    }

    protected function wtSave()
    {
        if (!$this->_post['id'] || !$this->_post['__icon'])
            return 3;

        $writeFile = function($file, $content)
        {
            $success = false;
            if ($handle = @fOpen($file, "w"))
            {
                if (fWrite($handle, $content))
                    $success = true;

                fClose($handle);
            }
            else
                die('me no file');

            if ($success)
                @chmod($file, Util::FILE_ACCESS);

            return $success;
        };


        // save to db

        DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE id = ?d', $this->_post['id']);
        DB::Aowow()->query('UPDATE ?_account_weightscales SET `icon`= ? WHERE `id` = ?d', $this->_post['__icon'], $this->_post['id']);

        foreach (explode(',', $this->_post['scale']) as $s)
        {
            list($k, $v) = explode(':', $s);

            if (!in_array($k, Util::$weightScales) || $v < 1)
                continue;

            if (DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $this->_post['id'], $k, $v) === null)
                return 1;
        }


        // write dataset

        $wtPresets = [];
        $scales    = DB::Aowow()->select('SELECT id, name, icon, class FROM ?_account_weightscales WHERE userId = 0 ORDER BY class, id ASC');

        foreach ($scales as $s)
        {
            $weights = DB::Aowow()->selectCol('SELECT field AS ARRAY_KEY, val FROM ?_account_weightscale_data WHERE id = ?d', $s['id']);
            if (!$weights)
                continue;

            $wtPresets[$s['class']]['pve'][$s['name']] = array_merge(['__icon' => $s['icon']], $weights);
        }

        $toFile = "var wt_presets = ".Util::toJSON($wtPresets).";";
        $file   = 'datasets/weight-presets';

        if (!$writeFile($file, $toFile))
            return 2;


        // all done

        return 0;
    }

    protected function checkId($val)
    {
        // expecting id-list
        if (preg_match('/\d+(,\d+)*/', $val))
            return array_map('intVal', explode(',', $val));

        return null;
    }

    protected function checkKey($val)
    {
        // expecting string
        if (preg_match('/[^a-z0-9_\.\-]/i', $val))
            return '';

        return strtolower($val);
    }

    protected function checkUser($val)
    {
        $n = Util::lower(trim(urldecode($val)));

        if (User::isValidName($n))
            return $n;

        return null;
    }

    protected function checkScale($val)
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return $val;

        return null;
    }

    private function confOnChange($key, $val, &$msg)
    {
        $fn = $buildList = null;

        switch ($key)
        {
            case 'battlegroup':
                $buildList = 'realms,realmMenu';
                break;
            case 'name_short':
                $buildList = 'searchboxBody,demo,searchplugin';
                break;
            case 'site_host':
                $buildList = 'searchplugin,demo,power,searchboxBody';
                break;
            case 'static_host':
                $buildList = 'searchplugin,power,searchboxBody,searchboxScript';
                break;
            case 'locales':
                $buildList = 'locales';
                $msg .= ' * remember to rebuild all static files for the language you just added.<br />';
                $msg .= ' * you can speed this up by supplying the regionCode to the setup: <pre class="q1">--locales=<regionCodes,> -f</pre>';
                break;
            case 'profiler_queue':
                $fn = function($x) use (&$msg) {
                    if (!$x)
                        return true;

                    return Profiler::queueStart($msg);
                };
                break;
            default:                                        // nothing to do, everything is fine
                return true;
        }

        if ($buildList)
        {
            // we need to use exec as build() can only be run from CLI
            exec('php aowow --build='.$buildList, $out);
            foreach ($out as $o)
                if (strstr($o, 'ERR'))
                    $msg .= explode('0m]', $o)[1]."<br />\n";
        }

        return $fn ? $fn($val) : true;
    }
}
