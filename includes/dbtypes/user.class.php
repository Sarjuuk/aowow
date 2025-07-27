<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class UserList extends DBTypeList
{
    public static int    $type       = Type::USER;
    public static string $brickFile  = 'user';
    public static string $dataTable  = '';
    public static int    $contribute = CONTRIBUTE_NONE;

    protected string $queryBase = 'SELECT *, a.`id` AS ARRAY_KEY FROM ?_account a';
    protected array  $queryOpts = array(
                        'a' => [['r']],
                        'r' => ['j' => ['?_account_reputation r ON r.`userId` = a.`id`', true], 's' => ', IFNULL(SUM(r.`amount`), 0) AS "reputation"', 'g' => 'a.`id`']
                    );

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->curTpl['username']] = array(
                'border'     => 0,                          // border around avatar (rarityColors)
                'roles'      => $this->curTpl['userGroups'],
                'joined'     => date(Util::$dateFormatInternal, $this->curTpl['joinDate']),
                'posts'      => 0,                          // forum posts
             // 'gold'       => 0,                          // achievement system
             // 'silver'     => 0,                          // achievement system
             // 'copper'     => 0,                          // achievement system
                'reputation' => $this->curTpl['reputation']
            );

            // custom titles (only seen on user page..?)
            if ($_ = $this->curTpl['title'])
                $data[$this->curTpl['username']]['title'] = $_;

            if ($_ = $this->curTpl['avatar'])
            {
                $data[$this->curTpl['username']]['avatar']     = is_numeric($_) ? 2 : 1;
                $data[$this->curTpl['username']]['avatarmore'] = $_;
            }

            // more optional data
            // sig: markdown formated string (only used in forum?)
            // border: seen as null|1|3 .. changes the border around the avatar (i suspect its meaning changed and got decoupled from premium-status with the introduction of patreon-status)
        }

        return [Type::USER => $data];
    }

    public function getListviewData() : array { return []; }
    public function renderTooltip() : ?string { return null; }

    public static function getName($id) : ?LocString { return null; }
}

?>
