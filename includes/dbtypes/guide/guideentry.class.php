<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideEntry extends DBTypeEntry
{
    public readonly  int    $cuFlags;
    public readonly  string $title;
    public readonly  string $name;
    public readonly  string $description;
    public readonly  int    $category;
    public readonly ?int    $classId;
    public readonly ?int    $specId;
    public readonly ?string $url;
    public readonly  int    $locale;
    public readonly  int    $status;
    public readonly  int    $rev;
    public readonly  int    $latest;
    public readonly  int    $roles;
    public readonly  int    $views;
    public readonly  int    $comments;
    public readonly ?int    $userId;
    public readonly ?string $author;
    public readonly  int    $date;
 // public readonly  int    $approveUserId;
 // public readonly  int    $approveDate;
 // public readonly  int    $deleteUserId;
 // public readonly  int    $deleteData;
    public readonly  int    $nvotes;
    public readonly  int    $rating;

    public static int    $dbType     = Type::GUIDE;
    public static string $brickFile  = 'guide';
    public static string $dataTable  = '::guides';
    public static int    $contribute = CONTRIBUTE_CO;

    private array $article   = [];
    private array $jsGlobals = [];

    public const /* string */ QUERY_BASE = 'SELECT g.*, g.`id` AS ARRAY_KEY FROM ::guides g';
    public const /* array  */ QUERY_OPTS = array(
        'g'  => [['a', 'c', 'ar'], 'g' => 'g.`id`'],
        'a'  => ['j' => ['::account a ON a.`id` = g.`userId`', true], 's' => ', IFNULL(a.`username`, "") AS "author"'],
        'c'  => ['j' => ['::comments c ON c.`type` = '.Type::GUIDE.' AND c.`typeId` = g.`id` AND (c.`flags` & '.CC_FLAG_DELETED.') = 0', true], 's' => ', COUNT(c.`id`) AS "comments"'],
        'ar' => ['j' => ['::articles ar ON ar.`type` = 300 AND ar.`typeId` = g.`id`'], 's' => ', MAX(ar.`rev`) AS "latest"']
    );

    public function __construct(int|array $initData, array $extraOpts = [])
    {
        parent::__construct($initData, $extraOpts);

        // not filled by batch operation
        if (is_int($initData))
        {
            $ratings = GuideMgr::getRatings([$this->id]);
            $this->setVoting($ratings[$this->id]['nvotes'] ?? 0, $ratings[$this->id]['rating'] ?? -1);
        }
    }

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        $data = array(
            'id'          => $this->id,
            'category'    => $this->category,
            'title'       => $this->title,
            'description' => $this->description,
            'sticky'      => !!($this->cuFlags & CC_FLAG_STICKY),
            'nvotes'      => $this->nvotes,
            'url'         => '?guide=' . ($this->url ?: $this->id),
            'status'      => $this->status,
            'author'      => $this->author,
            'authorroles' => $this->roles,
            'rating'      => $this->rating,
            'views'       => $this->views,
            'comments'    => $this->comments,
        //  'patch'       => $this->patch,                  // 30305 - patch is pointless, use date instead
            'date'        => $this->date,                   // ok
            'when'        => date(Util::$dateFormatInternal, $this->date)
        );

        if ($this->category == 1)
        {
            $data[$this->id]['classs'] = $this->classId;
            $data[$this->id]['spec']   = $this->specId;
        }

        return $data;
    }

    public function getJSGlobal(int $addMask = GLOBALINFO_ANY) : array
    {
        return $this->jsGlobals;
    }

    public function renderTooltip() : ?string
    {
        $specStr = '';

        if ($this->classId && $this->category == 1)
        {
            if ($c = $this->classId)
            {
                $n = Lang::game('cl', $c);
                $specStr .= '&nbsp;&nbsp;–&nbsp;&nbsp;<span class="icontiny c'.$c.'" style="background-image: url('.Cfg::get('STATIC_URL').'/images/wow/icons/tiny/class_'.ChrClass::tryFrom($c)->json().'.gif)">%s</span>';

                if (($s = $this->specId) > -1)
                {
                    $i = Game::$specIconStrings[$c][$s];
                    $n = '';
                    $specStr .= '<span class="icontiny c'.$c.'" style="background-image: url('.Cfg::get('STATIC_URL').'/images/wow/icons/tiny/'.$i.'.gif)">'.Lang::game('classSpecs', $c, $s).'</span>';
                }

                $specStr = sprintf($specStr, $n);
            }
        }

        $tt  = '<table><tr><td><div style="max-width: 320px"><b class="q">'.$this->title.'</b><br />';
        $tt .= '<table width="100%"><tr><td>'.Lang::game('guide').'</td><th>'.Lang::guide('byAuthor', [$this->author]).'</th></tr></table>';
        $tt .= '<table width="100%"><tr><td>'.Lang::guide('category', $this->category).$specStr.'</td><th>'.Lang::guide('patch').' 3.3.5</th></tr></table>';
        $tt .= '<div class="q" style="margin: 0.25em 0">'.$this->description.'</div>';
        $tt .= '</div></td></tr></table>';

        return $tt;
    }

    public function getArticle(int $rev = -1) : string
    {
        if ($rev < -1)
            $rev = -1;

        if (empty($this->article[$rev]))
        {
            $where = array(
                [DB::OR, [[DB::AND, [['`type` = %i', Type::GUIDE], ['`typeId` = %i', $this->id]]]]]
            );
            if ($this->url)
                $where[0][1][] = ['`url` = %s', $this->url];
            if ($rev >= 0)
                $where[] = ['`rev`= %i', $rev];

            $a = DB::Aowow()->selectRow('SELECT `article`, `rev` FROM ::articles WHERE %and ORDER BY `rev` DESC LIMIT 1', $where);

            $this->article[$a['rev']] = $a['article'];
            if ($this->article[$a['rev']])
            {
                Markup::parseTags($this->article[$a['rev']], $this->jsGlobals);
                return $this->article[$a['rev']];
            }
            else
                trigger_error('GuideList::getArticle - linked article is missing');
        }

        return $this->article[$rev] ?? '';
    }

    public function userCanView() : bool
    {
        //                                  is owner  || is staff
        return $this->userId == User::$id || User::isInGroup(U_GROUP_STAFF);
    }

    public function canBeViewed() : bool
    {
        //                                  currently approved    || has prev. approved version
        return $this->status == GuideMgr::STATUS_APPROVED || $this->rev > 0;
    }

    public function canBeReported() : bool
    {
        //                             not own guide  && is not archived
        return $this->userId != User::$id && $this->status != GuideMgr::STATUS_ARCHIVED;
    }

    public function setVoting(int $nVotes, int $rating) : void
    {
        $this->nvotes = $nVotes;
        $this->rating = $rating;
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `title` AS "name_loc0" FROM %n WHERE `id` = %i', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }
}

?>
