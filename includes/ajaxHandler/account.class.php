<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxAccount extends AjaxHandler
{
    protected $validParams = ['exclude', 'weightscales'];
    protected $_post       = array(
        // 'groups' => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'save'   => [FILTER_SANITIZE_NUMBER_INT, null],
        'delete' => [FILTER_SANITIZE_NUMBER_INT, null],
        'id'     => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkInt']],
        'name'   => [FILTER_CALLBACK,            ['options' => 'AjaxAccount::checkName']],
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

            $id = 0;

            if ($id = $this->_post['id'])
            {
                if (!DB::Aowow()->selectCell('SELECT 1 FROM ?_account_weightscales WHERE userId = ?d AND id = ?d', User::$id, $id))
                    return 0;

                DB::Aowow()->query('UPDATE ?_account_weightscales SET `name` = ? WHERE id = ?d', $this->_post['name'], $id);
            }
            else
            {
                $nScales = DB::Aowow()->selectCell('SELECT COUNT(id) FROM ?_account_weightscales WHERE userId = ?d', User::$id);
                if ($nScales >= 5)                          // more or less hard-defined in LANG.message_weightscalesaveerror
                    return 0;

                $id = DB::Aowow()->query('INSERT INTO ?_account_weightscales (`userId`, `name`) VALUES (?d, ?)', User::$id, $this->_post['name']);
            }

            DB::Aowow()->query('DELETE FROM ?_account_weightscale_data WHERE id = ?d', $id);

            foreach (explode(',', $this->_post['scale']) as $s)
            {
                list($k, $v) = explode(':', $s);
                if (!in_array($k, Util::$weightScales) || $v < 1)
                    continue;

                DB::Aowow()->query('INSERT INTO ?_account_weightscale_data VALUES (?d, ?, ?d)', $id, $k, $v);
            }

            return $id;
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

    protected function checkName($val)
    {
        $var = trim(urldecode($val));

        return filter_var($var, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_LOW);
    }
}
