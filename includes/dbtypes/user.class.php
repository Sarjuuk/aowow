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

        foreach ($this->iterate() as $userId => $__)
        {
            $data[$this->curTpl['username']] = array(
                'border'     => $this->getPremiumborder(),
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

            switch ($this->curTpl['avatar'])
            {
                case 1:
                    $data[$this->curTpl['username']]['avatar']     = $this->curTpl['avatar'];
                    $data[$this->curTpl['username']]['avatarmore'] = $this->curTpl['wowicon'];
                    break;
                case 2:
                    if ($this->isPremium())
                    {
                        if ($av = DB::Aowow()->selectCell('SELECT `id` FROM ?_account_avatars WHERE `userId` = ?d AND `current` = 1 AND `status` <> ?d', $userId, AvatarMgr::STATUS_REJECTED))
                        {
                            $data[$this->curTpl['username']]['avatar']     = $this->curTpl['avatar'];
                            $data[$this->curTpl['username']]['avatarmore'] = $av;
                        }
                    }
                    break;
            }

            // more optional data
            // sig: markdown formated string (only used in forum?)
        }

        return [Type::USER => $data];
    }

    // seen as null|1|3 .. changes the border around the avatar (chosen from account > premium tab?)
    // changed at the end of MoP. No longer a jsBool but index to Icon.premiumBorderClasses
    private function getPremiumBorder() : int
    {
        if (!$this->isPremium() || !$this->curTpl['avatar'])
            return 2;                                       // 2 is "none"

        return $this->curTpl['avatarborder'];
    }

    public function isPremium() : bool
    {
        return $this->curTpl['userGroups'] & U_GROUP_PREMIUM || $this->curTpl['reputation'] >= Cfg::get('REP_REQ_PREMIUM');
    }

    public function getListviewData() : array { return []; }
    public function renderTooltip() : ?string { return null; }

    public static function getName($id) : ?LocString { return null; }
}

?>
