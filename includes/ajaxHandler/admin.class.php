<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxAdmin extends AjaxHandler
{
    protected $validParams = ['siteconfig', 'weight-presets', 'spawn-override', 'comment'];
    protected $_get        = array(
        'action' => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkTextLine'      ],
        'id'     => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkIdListUnsigned'],
        'key'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxAdmin::checkKey'             ],
        'all'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkEmptySet'      ],
        'type'   => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'           ],
        'typeid' => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'           ],
        'user'   => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxAdmin::checkUser'            ],
        'val'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkTextBlob'      ],
        'guid'   => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'           ],
        'area'   => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'           ],
        'floor'  => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'           ]
    );
    protected $_post       = array(
        'alt'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkTextBlob'],
        'id'     => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'     ],
        'scale'  => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxAdmin::checkScale'     ],
        '__icon' => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxAdmin::checkKey'       ],
        'status' => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkInt'     ],
        'msg'    => ['filter' => FILTER_CALLBACK, 'options' => 'Aowow\AjaxHandler::checkTextBlob']
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (!$this->params)
            return;

        if ($this->params[0] == 'siteconfig' && $this->_get['action'])
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
        else if ($this->params[0] == 'comment')
        {
            if (!User::isInGroup(U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD))
                return;

            $this->handler = 'commentOutOfDate';
        }
    }

    protected function confAdd() : string
    {
        $key = trim($this->_get['key']);
        $val = trim(urldecode($this->_get['val']));

        return Cfg::add($key, $val);
    }

    protected function confRemove() : string
    {
        if (!$this->reqGET('key'))
            return 'invalid configuration option given';

        return Cfg::delete($this->_get['key']);
    }

    protected function confUpdate() : string
    {
        $key = trim($this->_get['key']);
        $val = trim(urldecode($this->_get['val']));

        return Cfg::set($key, $val);
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

        if (!in_array($type, [Type::NPC, Type::OBJECT, Type::SOUND, Type::AREATRIGGER, Type::ZONE]))
            return '-3';

        DB::Aowow()->query('REPLACE INTO ?_spawns_override VALUES (?d, ?d, ?d, ?d, ?d)', $type, $guid, $area, $floor, AOWOW_REVISION);

        if ($wPos = WorldPosition::getForGUID($type, $guid))
        {
            if ($point = WorldPosition::toZonePos($wPos[$guid]['mapId'], $wPos[$guid]['posX'], $wPos[$guid]['posY'], $area, $floor))
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
                        'SELECT -w.id AS `entry`, w.point AS `pointId`, w.position_x AS `posX`, w.position_y AS `posY` FROM creature_addon ca JOIN waypoint_data w ON w.id = ca.path_id WHERE ca.guid = ?d AND ca.path_id <> 0',
                        'SELECT `entry`, `pointId`, `location_x` AS `posX`, `location_y` AS `posY` FROM `script_waypoint` WHERE `entry` = ?d',
                        'SELECT `entry`, `pointId`, `position_x` AS `posX`, `position_y` AS `posY` FROM `waypoints` WHERE `entry` = ?d'
                    );

                    foreach ($jobs as $idx => $job)
                    {
                        if ($swp = DB::World()->select($job, $idx ? $wPos[$guid]['id'] : $guid))
                        {
                            foreach ($swp as $w)
                            {
                                if ($point = WorldPosition::toZonePos($wPos[$guid]['mapId'], $w['posX'], $w['posY'], $area, $floor))
                                {
                                    $p = array(
                                        'posX'   => $point[0]['posX'],
                                        'posY'   => $point[0]['posY'],
                                        'areaId' => $point[0]['areaId'],
                                        'floor'  => $point[0]['floor']
                                    );

                                    DB::Aowow()->query('UPDATE ?_creature_waypoints SET ?a WHERE `creatureOrPath` = ?d AND `point` = ?d', $p, $w['entry'], $w['pointId']);
                                }
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
        if (preg_match(Cfg::PATTERN_INV_CONF_KEY, $val))
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
}

?>
