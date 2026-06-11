<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');

/*
 * so this only exists because users can be expressed as jsGlobals so the comments header displays properly
 * this feels very wrong....
 */

class UserEntry extends DBTypeEntry
{
    // for now only include public info
    public readonly string $username;
    public readonly int    $joinDate;
    public readonly int    $userGroups;
    public readonly int    $avatar;
    public readonly int    $avatarborder;
    public readonly string $wowicon;
    public readonly string $title;
    public readonly int    $reputation;

    public static int    $dbType     = Type::USER;
    public static string $brickFile  = 'user';
    public static string $dataTable  = '';
    public static int    $contribute = CONTRIBUTE_NONE;

    public const /* string */ QUERY_BASE = 'SELECT *, a.`id` AS ARRAY_KEY FROM ::account a';
    public const /* array  */ QUERY_OPTS = array(
        'a' => [['r']],
        'r' => ['j' => ['::account_reputation r ON r.`userId` = a.`id`', true], 's' => ', IFNULL(SUM(r.`amount`), 0) AS "reputation"', 'g' => 'a.`id`']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'avatar':                              // can be null for some reason
                    $this->$k = $v ?? 0;
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array { return []; }

    public function getJSGlobal(int $addMask = 0) : array
    {
        $data = array(
            'border'     => $this->getPremiumborder(),
            'roles'      => $this->userGroups,
            'joined'     => date(Util::$dateFormatInternal, $this->joinDate),
            'posts'      => 0,                              // forum posts
         // 'gold'       => 0,                              // achievement system
         // 'silver'     => 0,                              // achievement system
         // 'copper'     => 0,                              // achievement system
            'reputation' => $this->reputation
        );

        // custom titles (only seen on user page..?)
        if ($this->title)
            $data['title'] = $this->title;

        switch ($this->avatar)
        {
            case 1:
                $data['avatar']     = $this->avatar;
                $data['avatarmore'] = $this->wowicon;
                break;
            case 2:
                if (!$this->isPremium())
                    break;

                if ($av = DB::Aowow()->selectCell('SELECT `id` FROM ::account_avatars WHERE `userId` = %i AND `current` = 1 AND `status` <> %i', $this->id, AvatarMgr::STATUS_REJECTED))
                {
                    $data['avatar']     = $this->avatar;
                    $data['avatarmore'] = $av;
                }
        }

        // more optional data
        // sig: markdown formated string (only used in forum?)

        return [Type::USER => [$this->curTpl['username'] => $data]];
    }

    public function renderTooltip() : ?string { return null; }

    public function isPremium() : bool
    {
        return $this->userGroups & U_GROUP_PREMIUM || $this->reputation >= Cfg::get('REP_REQ_PREMIUM');
    }

    // seen as null|1|3 .. changes the border around the avatar (chosen from account > premium tab?)
    // changed at the end of MoP. No longer a jsBool but index to Icon.premiumBorderClasses
    private function getPremiumBorder() : int
    {
        if (!$this->isPremium() || !$this->avatar)
            return 2;                                       // 2 is "none"

        return $this->avatarborder;
    }

    public static function getName(int $id) : ?LocString { return null; }
}

?>
