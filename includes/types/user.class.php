<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UserList extends BaseType
{
    public static   $type       = Type::USER;
    public static   $brickFile  = 'user';
    public static   $dataTable  = '';                         // doesn't have community content
    public static   $contribute = CONTRIBUTE_NONE;

    public          $sources    = [];

    protected       $queryBase  = 'SELECT *, a.id AS ARRAY_KEY FROM ?_account a';
    protected       $queryOpts  = array(
                        'a' => [['r']],
                        'r' => ['j' => ['?_account_reputation r ON r.userId = a.id', true], 's' => ', IFNULL(SUM(r.amount), 0) AS reputation', 'g' => 'a.id']
                    );

    public function getListviewData() { }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->curTpl['displayName']] = array(
                'border'     => 0,                          // border around avatar (rarityColors)
                'roles'      => $this->curTpl['userGroups'],
                'joined'     => date(Util::$dateFormatInternal, $this->curTpl['joinDate']),
                'posts'      => 0,                          // forum posts
                // 'gold'    => 0,                          // achievement system
                // 'silver'  => 0,                          // achievement system
                // 'copper'  => 0,                          // achievement system
                'reputation' => $this->curTpl['reputation']
            );

            // custom titles (only ssen on user page..?)
            if ($_ = $this->curTpl['title'])
                $data[$this->curTpl['displayName']]['title'] = $_;

            if ($_ = $this->curTpl['avatar'])
            {
                $data[$this->curTpl['displayName']]['avatar']     = is_numeric($_) ? 2 : 1;
                $data[$this->curTpl['displayName']]['avatarmore'] = $_;
            }

            // more optional data
            // sig: markdown formated string (only used in forum?)
            // border: seen as null|1|3 .. changes the border around the avatar (i suspect its meaning changed and got decupled from premium-status with the introduction of patreon-status)
        }

        return [Type::USER => $data];
    }

    public function renderTooltip() { }
}

?>
