<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GuideList extends DBTypeList
{
    use ListviewHelper;

    public const /* array */ STATUS_COLORS = array(
        GUIDE_STATUS_DRAFT    => '#71D5FF',
        GUIDE_STATUS_REVIEW   => '#FFFF00',
        GUIDE_STATUS_APPROVED => '#1EFF00',
        GUIDE_STATUS_REJECTED => '#FF4040',
        GUIDE_STATUS_ARCHIVED => '#FFD100'
    );

    public static int    $type       = Type::GUIDE;
    public static string $brickFile  = 'guide';
    public static string $dataTable  = '?_guides';
    public static int    $contribute = CONTRIBUTE_CO;

    private array $article   = [];
    private array $jsGlobals = [];

    protected string $queryBase = 'SELECT g.*, g.`id` AS ARRAY_KEY FROM ?_guides g';
    protected array  $queryOpts = array(
                        'g' => [['a', 'c'], 'g' => 'g.`id`'],
                        'a' => ['j' => ['?_account a ON a.`id` = g.`userId`', true], 's' => ', IFNULL(a.`username`, "") AS "author"'],
                        'c' => ['j' => ['?_comments c ON c.`type` = '.Type::GUIDE.' AND c.`typeId` = g.`id` AND (c.`flags` & '.CC_FLAG_DELETED.') = 0', true], 's' => ', COUNT(c.`id`) AS "comments"']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        if ($this->error)
            return;

        $ratings = DB::Aowow()->select('SELECT `entry` AS ARRAY_KEY, IFNULL(SUM(`value`), 0) AS `t`, IFNULL(COUNT(*), 0) AS `n`, IFNULL(MAX(IF(`userId` = ?d, `value`, 0)), 0) AS `s` FROM ?_user_ratings WHERE `type` = ?d AND `entry` IN (?a)', User::$id, RATING_GUIDE, $this->getFoundIDs());

        // post processing
        foreach ($this->iterate() as $id => &$_curTpl)
        {
            if (isset($ratings[$id]))
            {
                $_curTpl['nvotes'] = $ratings[$id]['n'];
                $_curTpl['rating'] = $ratings[$id]['n'] < 5 ? -1 : $ratings[$id]['t'] / $ratings[$id]['n'];
                $_curTpl['_self']  = $ratings[$id]['s'];
            }
            else
            {
                $_curTpl['nvotes'] = 0;
                $_curTpl['rating'] = -1;
            }
        }
    }

    public static function getName(int $id) : ?LocString
    {
        if ($n = DB::Aowow()->SelectRow('SELECT `title` AS "name_loc0" FROM ?# WHERE `id` = ?d', self::$dataTable, $id))
            return new LocString($n);
        return null;
    }

    public function getArticle(int $rev = -1) : string
    {
        if ($rev < -1)
            $rev = -1;

        if (empty($this->article[$rev]))
        {
            $a = DB::Aowow()->selectRow('SELECT `article`, `rev` FROM ?_articles WHERE ((`type` = ?d AND `typeId` = ?d){ OR `url` = ?}){ AND `rev`= ?d} ORDER BY `rev` DESC LIMIT 1',
                Type::GUIDE, $this->id, $this->getField('url') ?: DBSIMPLE_SKIP, $rev < 0 ? DBSIMPLE_SKIP : $rev);

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

    public function getListviewData(bool $addDescription = false) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'          => $this->id,
                'category'    => $this->getField('category'),
                'title'       => $this->getField('title'),
                'description' => $this->getField('description'),
                'sticky'      => !!($this->getField('cuFlags') & CC_FLAG_STICKY),
                'nvotes'      => $this->getField('nvotes'),
                'url'         => '?guide=' . ($this->getField('url') ?: $this->id),
                'status'      => $this->getField('status'),
                'author'      => $this->getField('author'),
                'authorroles' => $this->getField('roles'),
                'rating'      => $this->getField('rating'),
                'views'       => $this->getField('views'),
                'comments'    => $this->getField('comments'),
            //  'patch'       => $this->getField(''),       // 30305 - patch is pointless, use date instead
                'date'        => $this->getField('date'),   // ok
                'when'        => date(Util::$dateFormatInternal, $this->getField('date'))
            );

            if ($this->getField('category') == 1)
            {
                $data[$this->id]['classs'] = $this->getField('classId');
                $data[$this->id]['spec']   = $this->getField('specId');
            }
        }

        return $data;
    }

    public function userCanView() : bool
    {
        //                                  is owner  || is staff
        return $this->getField('userId') == User::$id || User::isInGroup(U_GROUP_STAFF);
    }

    public function canBeViewed() : bool
    {
        //                                  currently approved    || has prev. approved version
        return $this->getField('status') == GUIDE_STATUS_APPROVED || $this->getField('rev') > 0;
    }

    public function canBeReported() : bool
    {
        //                             not own guide  && is not archived
        return $this->getField('userId') != User::$id && $this->getField('status') != GUIDE_STATUS_ARCHIVED;
    }

    public function getJSGlobals(int $addMask = GLOBALINFO_ANY) : array
    {
        return $this->jsGlobals;
    }

    public function renderTooltip() : ?string
    {
        $specStr = '';

        if ($this->getField('classId') && $this->getField('category') == 1)
        {
            if ($c = $this->getField('classId'))
            {
                $n = Lang::game('cl', $c);
                $specStr .= '&nbsp;&nbsp;–&nbsp;&nbsp;<span class="icontiny c'.$c.'" style="background-image: url('.Cfg::get('STATIC_URL').'/images/wow/icons/tiny/class_'.ChrClass::tryFrom($c)->json().'.gif)">%s</span>';

                if (($s = $this->getField('specId')) > -1)
                {
                    $i = Game::$specIconStrings[$c][$s];
                    $n = '';
                    $specStr .= '<span class="icontiny c'.$c.'" style="background-image: url('.Cfg::get('STATIC_URL').'/images/wow/icons/tiny/'.$i.'.gif)">'.Lang::game('classSpecs', $c, $s).'</span>';
                }

                $specStr = sprintf($specStr, $n);
            }
        }

        $tt  = '<table><tr><td><div style="max-width: 320px"><b class="q">'.$this->getField('title').'</b><br />';
        $tt .= '<table width="100%"><tr><td>'.Lang::game('guide').'</td><th>'.Lang::guide('byAuthor', [$this->getField('author')]).'</th></tr></table>';
        $tt .= '<table width="100%"><tr><td>'.Lang::guide('category', $this->getField('category')).$specStr.'</td><th>'.Lang::guide('patch').' 3.3.5</th></tr></table>';
        $tt .= '<div class="q" style="margin: 0.25em 0">'.$this->getField('description').'</div>';
        $tt .= '</div></td></tr></table>';

        return $tt;
    }
}

?>
