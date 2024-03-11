<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxAccount extends AjaxHandler
{
    protected $validParams = ['exclude', 'weightscales', 'favorites'];
    protected $_post       = array(
        'groups'     => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'save'       => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'delete'     => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'id'         => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkIdList'],
        'name'       => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAccount::checkName'  ],
        'scale'      => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxAccount::checkScale' ],
        'reset'      => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'mode'       => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'type'       => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'add'        => ['filter' => FILTER_SANITIZE_NUMBER_INT],
        'remove'     => ['filter' => FILTER_SANITIZE_NUMBER_INT],
     // 'sessionKey' => ['filter' => FILTER_SANITIZE_NUMBER_INT]
    );
    protected $_get        = array(
        'locale' => ['filter' => FILTER_CALLBACK, 'options' => 'AjaxHandler::checkLocale']
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (is_numeric($this->_get['locale']))
            User::useLocale($this->_get['locale']);

        if (!$this->params || !User::$id)
            return;

        // select handler
        if ($this->params[0] == 'exclude')
            $this->handler = 'handleExclude';
        else if ($this->params[0] == 'weightscales')
            $this->handler = 'handleWeightscales';
        else if ($this->params[0] == 'favorites')
            $this->handler = 'handleFavorites';
    }

    protected function handleExclude() : void
    {
        if ($this->_post['mode'] == 1)                      // directly set exludes
        {
            $type = $this->_post['type'];
            $ids  = $this->_post['id'];

            if (!Type::exists($type) || empty($ids))
            {
                trigger_error('AjaxAccount::handleExclude - invalid type #'.$type.(empty($ids) ? ' or id-list empty' : ''), E_USER_ERROR);
                return;
            }

            // ready for some bullshit? here it comes!
            // we don't get signaled whether an id should be added to or removed from either includes or excludes
            // so we throw everything into one table and toggle the mode if its already in here

            $includes = DB::Aowow()->selectCol('SELECT typeId FROM ?_profiler_excludes WHERE type = ?d AND typeId IN (?a)', $type, $ids);

            foreach ($ids as $typeId)
                DB::Aowow()->query('INSERT INTO ?_account_excludes (`userId`, `type`, `typeId`, `mode`) VALUES (?a) ON DUPLICATE KEY UPDATE mode = (mode ^ 0x3)', array(
                    User::$id, $type, $typeId, in_array($typeId, $includes) ? 2 : 1
                ));

            return;
        }
        else if ($this->_post['reset'] == 1)                // defaults to unavailable
        {
            $mask = PR_EXCLUDE_GROUP_UNAVAILABLE;
            DB::Aowow()->query('DELETE FROM ?_account_excludes WHERE userId = ?d', User::$id);
        }
        else                                                // clamp to real groups
            $mask = $this->_post['groups'] & PR_EXCLUDE_GROUP_ANY;

        DB::Aowow()->query('UPDATE ?_account SET excludeGroups = ?d WHERE id = ?d', $mask, User::$id);
    }

    protected function handleWeightscales() : string
    {
        if ($this->_post['save'])
        {
            if (!$this->_post['scale'])
            {
                trigger_error('AjaxAccount::handleWeightscales - scaleId empty', E_USER_ERROR);
                return '0';
            }

            $id = 0;

            if ($this->_post['id'] && ($id = $this->_post['id'][0]))
            {
                if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account_weightscales WHERE userId = ?d AND id = ?d', User::$id, $id))
                {
                    trigger_error('AjaxAccount::handleWeightscales - scale #'.$id.' not in db or owned by user #'.User::$id, E_USER_ERROR);
                    return '0';
                }

                DB::Aowow()->query('UPDATE ?_account_weightscales SET `name` = ? WHERE id = ?d', $this->_post['name'], $id);
            }
            else
            {
                $nScales = DB::Aowow()->selectCell('SELECT COUNT(id) FROM ?_account_weightscales WHERE userId = ?d', User::$id);
                if ($nScales >= 5)                          // more or less hard-defined in LANG.message_weightscalesaveerror
                    return '0';

                $id = DB::Aowow()->query('INSERT INTO ?_account_weightscales (`userId`, `name`) VALUES (?d, ?)', User::$id, $this->_post['name']);
            }

            DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE id = ?d', $id);

            foreach (explode(',', $this->_post['scale']) as $s)
            {
                [$k, $v] = explode(':', $s);
                if (!in_array($k, Util::$weightScales) || $v < 1)
                    continue;

                DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $id, $k, $v);
            }

            return (string)$id;
        }
        else if ($this->_post['delete'] && $this->_post['id'] && $this->_post['id'][0])
            DB::Aowow()->query('DELETE FROM ?_account_weightscales WHERE id = ?d AND userId = ?d', $this->_post['id'][0], User::$id);
        else
        {
            trigger_error('AjaxAccount::handleWeightscales - malformed request received', E_USER_ERROR);
            return '0';
        }
    }

    protected function handleFavorites() : void
    {
        // omit usage of sessionKey
        if (count($this->_post['id']) != 1 || empty($this->_post['id'][0]))
        {
            trigger_error('AjaxAccount::handleFavorites - malformed request received', E_USER_ERROR);
            return;
        }

        $typeId = $this->_post['id'][0];

        if ($type = $this->_post['add'])
        {
            $tc = Type::newList($type, [['id', $typeId]]);
            if (!$tc || $tc->error)
            {
                trigger_error('AjaxAccount::handleFavorites - invalid typeId #'.$typeId.' for type #'.$type, E_USER_ERROR);
                return;
            }

            DB::Aowow()->query('INSERT INTO ?_account_favorites (`userId`, `type`, `typeId`) VALUES (?d, ?d, ?d)', User::$id, $type, $typeId);
        }
        else if ($type = $this->_post['remove'])
            DB::Aowow()->query('DELETE FROM ?_account_favorites WHERE `userId` = ?d AND `type` = ?d AND `typeId` = ?d', User::$id, $type, $typeId);
    }

    protected static function checkScale(string $val) : string
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return $val;

        return '';
    }

    protected static function checkName(string $val) : string
    {
        $var = trim(urldecode($val));

        return filter_var($var, FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW);
    }
}

?>
