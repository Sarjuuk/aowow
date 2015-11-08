<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');

class AjaxAccount extends AjaxHandler
{
    protected $validParams = ['exclude', 'weightscales'];
    protected $_post       = array(
        // 'groups' => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'save'   => [FILTER_SANITIZE_NUMBER_INT, null],
        'delete' => [FILTER_SANITIZE_NUMBER_INT, null],
        'id'     => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'name'   => [FILTER_SANITIZE_STRING,     FILTER_FLAG_STRIP_LOW],
        'scale'  => [FILTER_CALLBACK,            ['options' => 'AjaxAccount::checkScale']],
    );
    protected $_get        = array(
        'locale' => [FILTER_CALLBACK, ['options' => 'AjaxHandler::checkLocale']]
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
    }

    protected function handleExclude()
    {
        // profiler completion exclude handler
        // $this->_post['groups'] = bitMask of excludeGroupIds when using .. excludeGroups .. duh
        // should probably occur in g_user.excludegroups (dont forget to also set g_users.settings = {})
        return '';
    }

    protected function handleWeightscales()
    {
        if ($this->_post['save'])
        {
            if (!$this->_post['scale'])
                return 0;

            if (!$this->_post['id'])
            {
                $res = DB::Aowow()->selectRow('SELECT MAX(id) AS max, count(id) AS num FROM ?_account_weightscales WHERE userId = ?d', User::$id);
                if ($res['num'] < 5)            // more or less hard-defined in LANG.message_weightscalesaveerror
                    $this->post['id'] = ++$res['max'];
                else
                    return 0;
            }

            if (DB::Aowow()->query('REPLACE INTO ?_account_weightscales VALUES (?d, ?d, ?, ?)', $this->_post['id'], User::$id, $this->_post['name'], $this->_post['scale']))
                return $this->_post['id'];
            else
                return 0;
        }
        else if ($this->_post['delete'] && $this->_post['id'])
            DB::Aowow()->query('DELETE FROM ?_account_weightscales WHERE id = ?d AND userId = ?d', $this->_post['id'], User::$id);
        else
            return 0;
    }

    protected function checkScale($val)
    {
        if (preg_match('/^((\w+:\d+)(,\w+:\d+)*)$/', $val))
            return $val;

        return null;
    }
}