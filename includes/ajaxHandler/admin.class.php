<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxAdmin extends AjaxHandler
{
    protected $validParams = ['screenshots', 'siteconfig', 'weight-presets', 'spawn-override', 'guide', 'comment'];
    protected $_get        = array(
        'action' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextLine'      ],
        'id'     => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkIdListUnsigned'],
        'key'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAdmin::checkKey'             ],
        'all'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkEmptySet'      ],
        'type'   => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'           ],
        'typeid' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'           ],
        'user'   => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAdmin::checkUser'            ],
        'val'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextBlob'      ],
        'guid'   => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'           ],
        'area'   => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'           ],
        'floor'  => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'           ]
    );
    protected $_post       = array(
        'alt'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextBlob'],
        'id'     => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'     ],
        'scale'  => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAdmin::checkScale'     ],
        '__icon' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAdmin::checkKey'       ],
        'status' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkInt'     ],
        'msg'    => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkTextBlob']
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params)
            return;

        if ($this->params[0] == 'screenshots' && $this->_get['action'])
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
        else if ($this->params[0] == 'siteconfig' && $this->_get['action'])
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
        else if ($this->params[0] == 'weight-presets' && $this->_get['action'])
        {
            if (!User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN | U_GROUP_BUREAU))
                return;

            if ($this->_get['action'] == 'save')
                $this->handler = 'wtSave';
        }
        else if ($this->params[0] == 'spawn-override')
        {
            if (!User::isInGroup(U_GROUP_MODERATOR))
                return;

            $this->handler = 'spawnPosFix';
        }
        else if ($this->params[0] == 'guide')
        {
            if (!User::isInGroup(U_GROUP_STAFF))
                return;

            $this->handler = 'guideManage';
        }
        else if ($this->params[0] == 'comment')
        {
            if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD))
                return;

            $this->handler = 'commentOutOfDate';
        }
    }

    // get all => null (optional)
    // evaled response .. UNK
    protected function ssList() : string
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
    protected function ssManage() : string
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
    protected function ssEditAlt() : void
    {
        // doesn't need to be htmlEscaped, ths javascript does that
        if ($this->_get['id'] && $this->_post['alt'] !== null)
            DB::Aowow()->query('UPDATE ?_screenshots SET caption = ? WHERE id = ?d', trim($this->_post['alt']), $this->_get['id'][0]);
    }

    // get: id => comma-separated SSids
    // resp: ''
    protected function ssApprove() : void
    {
        if (!$this->reqGET('id'))
        {
            trigger_error('AjaxAdmin::ssApprove - screenshotId empty', E_USER_ERROR);
            return;
        }

        // create resized and thumb version of screenshot
        $resized = [772, 618];
        $thumb   = [150, 150];
        $path    = 'static/uploads/screenshots/%s/%d.jpg';

        foreach ($this->_get['id'] as $id)
        {
            // must not be already approved
            if ($ssEntry = DB::Aowow()->selectRow('SELECT userIdOwner, date, type, typeId FROM ?_screenshots WHERE (status & ?d) = 0 AND id = ?d', CC_FLAG_APPROVED, $id))
            {
                // should also error-log
                if (!file_exists(sprintf($path, 'pending', $id)))
                {
                    trigger_error('AjaxAdmin::ssApprove - screenshot #'.$id.' exists in db but not as file', E_USER_ERROR);
                    continue;
                }

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
                Util::gainSiteReputation($ssEntry['userIdOwner'], SITEREP_ACTION_UPLOAD, ['id' => $id, 'what' => 1, 'date' => $ssEntry['date']]);
                // flag DB entry as having screenshots
                if ($tbl = Type::getClassAttrib($ssEntry['type'], 'dataTable'))
                    DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags | ?d WHERE id = ?d', CUSTOM_HAS_SCREENSHOT, $ssEntry['typeId']);
            }
            else
                trigger_error('AjaxAdmin::ssApprove - screenshot #'.$id.' not in db or already approved', E_USER_ERROR);
        }

        return;
    }

    // get: id => comma-separated SSids
    // resp: ''
    protected function ssSticky() : void
    {
        if (!$this->reqGET('id'))
        {
            trigger_error('AjaxAdmin::ssSticky - screenshotId empty', E_USER_ERROR);
            return;
        }

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
    }

    // get: id => comma-separated SSids
    // resp: ''
    // 2 steps: 1) remove from sight, 2) remove from disk
    protected function ssDelete() : void
    {
        if (!$this->reqGET('id'))
        {
            trigger_error('AjaxAdmin::ssDelete - screenshotId empty', E_USER_ERROR);
            return;
        }

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
            if ($toUnflag && ($tbl = Type::getClassAttrib($type, 'dataTable')))
                DB::Aowow()->query('UPDATE '.$tbl.' SET cuFlags = cuFlags & ~?d WHERE id IN (?a)', CUSTOM_HAS_SCREENSHOT, array_keys($toUnflag));
        }
    }

    // get: id => ssId, typeid => typeId    (but not type..?)
    // resp: ''
    protected function ssRelocate() : void
    {
        if (!$this->reqGET('id', 'typeid'))
        {
            trigger_error('AjaxAdmin::ssRelocate - screenshotId or typeId empty', E_USER_ERROR);
            return;
        }

        $id                     = $this->_get['id'][0];
        [$type, $oldTypeId] = array_values(DB::Aowow()->selectRow('SELECT type, typeId FROM ?_screenshots WHERE id = ?d', $id));
        $typeId                 = (int)$this->_get['typeid'];

        $tc = Type::newList($type, [['id', $typeId]]);
        if ($tc && !$tc->error)
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
        else
            trigger_error('AjaxAdmin::ssRelocate - invalid typeId #'.$typeId.' for type #'.$type, E_USER_ERROR);
    }

    protected function confAdd() : string
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

    protected function confRemove() : string
    {
        if (!$this->reqGET('key'))
            return 'invalid configuration option given';

        if (DB::Aowow()->query('DELETE FROM ?_config WHERE `key` = ? AND (`flags` & ?d) = 0', $this->_get['key'], CON_FLAG_PERSISTENT))
            return '';
        else
            return 'option name is either protected or was not found';
    }

    protected function confUpdate() : string
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
        else if ($cfg['flags'] & CON_FLAG_TYPE_BOOL && $val != '1')
            $val = '0';

        DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $val, $key);
        if (!$this->confOnChange($key, $val, $msg))
            DB::Aowow()->query('UPDATE ?_config SET `value` = ? WHERE `key` = ?', $cfg['value'], $key);

        return $msg;
    }

    protected function wtSave() : string
    {
        if (!$this->reqPOST('id', '__icon'))
            return '3';

        // save to db
        DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE id = ?d', $this->_post['id']);
        DB::Aowow()->query('UPDATE ?_account_weightscales SET `icon`= ? WHERE `id` = ?d', $this->_post['__icon'], $this->_post['id']);

        foreach (explode(',', $this->_post['scale']) as $s)
        {
            [$k, $v] = explode(':', $s);

            if (!in_array($k, Util::$weightScales) || $v < 1)
                continue;

            if (DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $this->_post['id'], $k, $v) === null)
                return '1';
        }

        // write dataset
        exec('php aowow --build=weightPresets', $out);
        foreach ($out as $o)
            if (strstr($o, 'ERR'))
                return '2';

        // all done
        return '0';
    }

    protected function spawnPosFix() : string
    {
        if (!$this->reqGET('type', 'guid', 'area', 'floor'))
            return '-4';

        $guid  = $this->_get['guid'];
        $type  = $this->_get['type'];
        $area  = $this->_get['area'];
        $floor = $this->_get['floor'];

        if (!in_array($type, [Type::NPC, Type::OBJECT, Type::SOUND, Type::AREATRIGGER]))
            return '-3';

        DB::Aowow()->query('REPLACE INTO ?_spawns_override VALUES (?d, ?d, ?d, ?d, ?d)', $type, $guid, $area, $floor, AOWOW_REVISION);

        if ($wPos = Game::getWorldPosForGUID($type, $guid))
        {
            if ($point = Game::worldPosToZonePos($wPos[$guid]['mapId'], $wPos[$guid]['posX'], $wPos[$guid]['posY'], $area, $floor))
            {
                $updGUIDs = [$guid];
                $newPos   = array(
                    'posX'   => $point[0]['posX'],
                    'posY'   => $point[0]['posY'],
                    'areaId' => $point[0]['areaId'],
                    'floor'  => $point[0]['floor']
                );

                // if creature try for waypoints
                if ($type == Type::NPC)
                {
                    $jobs = array(
                        'SELECT -w.id AS `entry`, w.point AS `pointId`, w.position_y AS `posX`, w.position_x AS `posY` FROM creature_addon ca JOIN waypoint_data w ON w.id = ca.path_id WHERE ca.guid = ?d AND ca.path_id <> 0',
                        'SELECT `entry`, `pointId`, `location_y` AS `posX`, `location_x` AS `posY` FROM `script_waypoint` WHERE `entry` = ?d',
                        'SELECT `entry`, `pointId`, `position_y` AS `posX`, `position_x` AS `posY` FROM `waypoints` WHERE `entry` = ?d'
                    );

                    foreach ($jobs as $idx => $job)
                    {
                        if ($swp = DB::World()->select($job, $idx ? $wPos[$guid]['id'] : $guid))
                        {
                            foreach ($swp as $w)
                            {
                                if ($point = Game::worldPosToZonePos($wPos[$guid]['mapId'], $w['posX'], $w['posY'], $area, $floor))
                                {
                                    $p = array(
                                        'posX'   => $point[0]['posX'],
                                        'posY'   => $point[0]['posY'],
                                        'areaId' => $point[0]['areaId'],
                                        'floor'  => $point[0]['floor']
                                    );
                                }
                                DB::Aowow()->query('UPDATE ?_creature_waypoints SET ?a WHERE `creatureOrPath` = ?d AND `point` = ?d', $p, $w['entry'], $w['pointId']);
                            }
                        }
                    }

                    // also move linked vehicle accessories (on the very same position)
                    $updGUIDs = array_merge($updGUIDs, DB::Aowow()->selectCol('SELECT s2.guid FROM ?_spawns s1 JOIN ?_spawns s2 ON s1.posX = s2.posX AND s1.posY = s2.posY AND
                        s1.areaId = s2.areaId AND s1.floor = s2.floor AND s2.guid < 0 WHERE s1.guid = ?d', $guid));
                }

                DB::Aowow()->query('UPDATE ?_spawns SET ?a WHERE `type` = ?d AND `guid` IN (?a)', $newPos, $type, $updGUIDs);

                return '1';
            }

            return '-2';
        }

        return '-1';
    }

    protected function guideManage() : string
    {
        $update = function (int $id, int $status, ?string $msg = null) : bool
        {
            if (!DB::Aowow()->query('UPDATE ?_guides SET `status` = ?d WHERE `id` = ?d', $status, $id))
                return false;

            // set display rev to latest
            if ($status == GUIDE_STATUS_APPROVED)
                DB::Aowow()->query('UPDATE ?_guides SET `rev` = (SELECT `rev` FROM ?_articles WHERE `type` = ?d AND `typeId` = ?d ORDER BY `rev` DESC LIMIT 1), `approveUserId` = ?d, `approveDate` = ?d WHERE `id` = ?d', Type::GUIDE, $id, User::$id, time(), $id);

            DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `date`, `userId`, `status`) VALUES (?d, ?d, ?d, ?d)', $id, time(), User::$id, $status);
            if ($msg)
                DB::Aowow()->query('INSERT INTO ?_guides_changelog (`id`, `date`, `userId`, `msg`)    VALUES (?d, ?d, ?d, ?)' , $id, time(), User::$id, $msg);
            return true;
        };

        if (!$this->_post['id'])
            trigger_error('AjaxHander::guideManage - malformed request: id: '.$this->_post['id'].', status: '.$this->_post['status']);
        else
        {
            $guide = DB::Aowow()->selectRow('SELECT `userId`, `status` FROM ?_guides WHERE `id` = ?d', $this->_post['id']);
            if (!$guide)
                trigger_error('AjaxHander::guideManage - guide #'.$this->_post['id'].' not found');
            else
            {
                if ($this->_post['status'] == $guide['status'])
                    trigger_error('AjaxHander::guideManage - guide #'.$this->_post['id'].' already has status #'.$this->_post['status']);
                else
                {
                    if ($this->_post['status'] == GUIDE_STATUS_APPROVED)
                    {
                        if ($update($this->_post['id'], GUIDE_STATUS_APPROVED, $this->_post['msg']))
                        {
                            Util::gainSiteReputation($guide['userId'], SITEREP_ACTION_ARTICLE, ['id' => $this->_post['id']]);
                            return '1';
                        }
                        else
                            return '-2';
                    }
                    else if ($this->_post['status'] == GUIDE_STATUS_REJECTED)
                        return $update($this->_post['id'], GUIDE_STATUS_REJECTED, $this->_post['msg']) ? '1' : '-2';
                    else
                        trigger_error('AjaxHander::guideManage - unhandled status change request');
                }
            }
        }

        return '-1';
    }

    protected function commentOutOfDate() : string
    {
        $ok = false;
        switch ($this->_post['status'])
        {
            case 0:                                         // up to date
                if ($ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = `flags` & ~?d WHERE `id` = ?d', CC_FLAG_OUTDATED, $this->_post['id']))
                    if ($rep = new Report(Report::MODE_COMMENT, Report::CO_OUT_OF_DATE, $this->_post['id']))
                        $rep->close(Report::STATUS_CLOSED_WONTFIX);
                break;
            case 1:                                         // outdated, mark as deleted and clear other flags (sticky + outdated)
                if ($ok = DB::Aowow()->query('UPDATE ?_comments SET `flags` = ?d, `deleteUserId` = ?d, `deleteDate` = ?d WHERE `id` = ?d', CC_FLAG_DELETED, User::$id, time(), $this->_post['id']))
                    if ($rep = new Report(Report::MODE_COMMENT, Report::CO_OUT_OF_DATE, $this->_post['id']))
                        $rep->close(Report::STATUS_CLOSED_SOLVED);
        break;
            default:
                trigger_error('AjaxHandler::comentOutOfDate - called with invalid status');
        }

        return $ok ? '1' : '0';
    }


    /***************************/
    /* additional input filter */
    /***************************/

    protected static function checkKey(string $val) : string
    {
        // expecting string
        if (preg_match('/[^a-z0-9_\.\-]/i', $val))
            return '';

        return strtolower($val);
    }

    protected static function checkUser($val) : string
    {
        $n = Util::lower(trim(urldecode($val)));

        if (User::isValidName($n))
            return $n;

        return '';
    }

    protected static function checkScale($val) : string
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return $val;

        return '';
    }


    /**********/
    /* helper */
    /**********/

    private static function confOnChange(string $key, string $val, string &$msg) : bool
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
            case 'contact_email':
                $buildList = 'markup';
                break;
            case 'locales':
                $buildList = 'locales';
                $msg .= ' * remember to rebuild all static files for the language you just added.<br />';
                $msg .= ' * you can speed this up by supplying the regionCode to the setup: <pre class="q1">--locales=<regionCodes,> -f</pre>';
                break;
            case 'profiler_enable':
                $buildList = 'realms,realmMenu';
                $fn = function($x) use (&$msg) {
                    if (!$x)
                        return true;

                    return Profiler::queueStart($msg);
                };
                break;
            case 'acc_auth_mode':
                $fn = function($x) use (&$msg) {
                    if ($x == 1 && !extension_loaded('gmp'))
                    {
                        $msg .= 'PHP extension GMP is required to use TrinityCore as auth source, but is not currently enabled.<br />';
                        return false;
                    }

                    return true;
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

?>
