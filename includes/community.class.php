<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/************
* get Community Content
************/

/*
    {id:115,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Guide to the Fury Warrior (Part 1)',videoType:1,videoId:'VUvxFvVmttg',type:13,typeId:1},
    {id:116,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Guide to the Fury Warrior (Part 2)',videoType:1,videoId:'VEfnuIcq7n8',type:13,typeId:1},
    {id:117,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Protection Warrior Guide',videoType:1,videoId:'vF-7kmvJZXY',type:13,typeId:1,sticky:1}
*/

/* todo: administration of content */

class CommunityContent
{
    private static $jsGlobals = [];
    private static $subjCache = [];

    private static $commentQuery = '
        SELECT
            c.*,
            a1.displayName AS user,
            a2.displayName AS editUser,
            a3.displayName AS deleteUser,
            a4.displayName AS responseUser,
            IFNULL(SUM(cr.value), 0) AS rating,
            SUM(IF(cr.userId > 0 AND cr.userId = ?d, cr.value, 0)) AS userRating,
            SUM(IF( r.userId > 0 AND  r.userId = ?d, 1, 0)) AS userReported
        FROM
            ?_comments c
        JOIN
            ?_account a1 ON c.userId = a1.id
        LEFT JOIN
            ?_account a2 ON c.editUserId = a2.id
        LEFT JOIN
            ?_account a3 ON c.deleteUserId = a3.id
        LEFT JOIN
            ?_account a4 ON c.responseUserId = a4.id
        LEFT JOIN
            ?_comments_rates cr ON c.id = cr.commentId
        LEFT JOIN
            ?_reports r ON r.subject = c.id AND r.mode = 1 AND r.reason = 19
        WHERE
            c.replyTo = ?d AND c.type = ?d AND c.typeId = ?d AND
            ((c.flags & ?d) = 0 OR c.userId = ?d OR ?d)
        GROUP BY
            c.id
        ORDER BY
            rating ASC
    ';

    private static $previewQuery = '
        SELECT
            c.id,
            c.body AS preview,
            c.date,
            c.replyTo AS commentid,
            UNIX_TIMESTAMP() - c.date AS elapsed,
            IF(c.flags & ?d, 1, 0) AS deleted,
            IF(c.type <> 0, c.type, c2.type) AS type,
            IF(c.typeId <> 0, c.typeId, c2.typeId) AS typeId,
            IFNULL(SUM(cr.value), 0) AS rating,
            a.displayName AS user
        FROM
            ?_comments c
        JOIN
            ?_account a ON c.userId = a.id
        LEFT JOIN
            ?_comments_rates cr ON cr.commentId = c.id AND cr.userId <> 0
        LEFT JOIN
            ?_comments c2 ON c.replyTo = c2.id
        WHERE
            {c.userId = ?d AND}
            {c.replyTo <> ?d AND}
            {c.replyTo = ?d AND}
            ((c.flags & ?d) = 0 OR c.userId = ?d OR ?d)
        GROUP BY
            c.id
        ORDER BY
            date DESC
        LIMIT
            ?d
    ';

    private static function addSubject($type, $typeId)
    {
        if (!isset(self::$subjCache[$type][$typeId]))
            self::$subjCache[$type][$typeId] = 0;
    }

    private static function getSubjects()
    {
        foreach (self::$subjCache as $type => $ids)
        {
            $_ = array_filter(array_keys($ids), 'is_numeric');
            if (!$_)
                continue;

            $cnd = [CFG_SQL_LIMIT_NONE, ['id', $_]];

            switch ($type)
            {
                case TYPE_NPC:         $obj = new CreatureList($cnd);    break;
                case TYPE_OBJECT:      $obj = new GameobjectList($cnd);  break;
                case TYPE_ITEM:        $obj = new ItemList($cnd);        break;
                case TYPE_ITEMSET:     $obj = new ItemsetList($cnd);     break;
                case TYPE_QUEST:       $obj = new QuestList($cnd);       break;
                case TYPE_SPELL:       $obj = new SpellList($cnd);       break;
                case TYPE_ZONE:        $obj = new ZoneList($cnd);        break;
                case TYPE_FACTION:     $obj = new FactionList($cnd);     break;
                case TYPE_PET:         $obj = new PetList($cnd);         break;
                case TYPE_ACHIEVEMENT: $obj = new AchievementList($cnd); break;
                case TYPE_TITLE:       $obj = new TitleList($cnd);       break;
                case TYPE_WORLDEVENT:  $obj = new WorldEventList($cnd);  break;
                case TYPE_CLASS:       $obj = new CharClassList($cnd);   break;
                case TYPE_RACE:        $obj = new CharRaceList($cnd);    break;
                case TYPE_SKILL:       $obj = new SkillList($cnd);       break;
                case TYPE_CURRENCY:    $obj = new CurrencyList($cnd);    break;
                case TYPE_EMOTE:       $obj = new EmoteList($cnd);       break;
                case TYPE_ENCHANTMENT: $obj = new EnchantmentList($cnd); break;
                case TYPE_SOUND:       $obj = new SoundList($cnd);       break;
                case TYPE_ICON:        $obj = new IconList($cnd);        break;
                default: continue 2;
            }

            foreach ($obj->iterate() as $id => $__)
                self::$subjCache[$type][$id] = $obj->getField('name', true);
        }
    }

    public static function getCommentPreviews($params = [], &$nFound = 0)
    {
        /*
            purged:0,           <- doesnt seem to be used anymore
            domain:'live'       <- irrelevant for our case
        */

        $comments  = DB::Aowow()->selectPage(
            $nFound,
            self::$previewQuery,
            CC_FLAG_DELETED,
             empty($params['user'])    ? DBSIMPLE_SKIP : $params['user'],
             empty($params['replies']) ? DBSIMPLE_SKIP : 0, // i dont know, how to switch the sign around
            !empty($params['replies']) ? DBSIMPLE_SKIP : 0,
            CC_FLAG_DELETED,
            User::$id,
            User::isInGroup(U_GROUP_COMMENTS_MODERATOR),
            CFG_SQL_LIMIT_DEFAULT
        );

        foreach ($comments as $c)
            self::addSubject($c['type'], $c['typeId']);

        self::getSubjects();

        foreach ($comments as $idx => &$c)
        {
            if (!empty(self::$subjCache[$c['type']][$c['typeId']]))
            {
                // apply subject
                $c['subject'] = self::$subjCache[$c['type']][$c['typeId']];

                // format date
                $c['date'] = date(Util::$dateFormatInternal, $c['date']);

                // remove commentid if not looking for replies
                if (empty($params['replies']))
                    unset($c['commentid']);

                // format text for listview
                $c['preview'] = Lang::trimTextClean($c['preview']);
            }
            else
            {
                trigger_error('Comment '.$c['id'].' belongs to nonexistant subject.', E_USER_NOTICE);
                unset($comments[$idx]);
            }
        }

        return $comments;
    }

    public static function getCommentReplies($commentId, $limit = 0, &$nFound = 0)
    {
        $replies = [];
        $query = $limit > 0 ? self::$commentQuery.' LIMIT '.$limit : self::$commentQuery;

        // get replies
        $results = DB::Aowow()->selectPage($nFound, $query, User::$id, User::$id, $commentId, 0, 0, CC_FLAG_DELETED, User::$id, User::isInGroup(U_GROUP_COMMENTS_MODERATOR));
        foreach ($results as $r)
        {
            (new Markup($r['body']))->parseGlobalsFromText(self::$jsGlobals);

            $reply = array(
                'commentid'    => $commentId,
                'id'           => $r['id'],
                'body'         => $r['body'],
                'username'     => $r['user'],
                'roles'        => $r['roles'],
                'creationdate' => date(Util::$dateFormatInternal, $r['date']),
                'lasteditdate' => date(Util::$dateFormatInternal, $r['editDate']),
                'rating'       => (string)$r['rating']
            );

            if ($r['userReported'])
                $reply['reportedByUser'] = true;

            if ($r['userRating'] > 0)
                $reply['votedByUser'] = true;
            else if ($r['userRating'] < 0)
                $reply['downvotedByUser'] = true;

            $replies[] = $reply;
        }

        return $replies;
    }

    public static function getScreenshotsForManager($type, $typeId, $userId = 0)
    {
        $screenshots = DB::Aowow()->select('
            SELECT    s.id, a.displayName AS user, s.date, s.width, s.height, s.type, s.typeId, s.caption, s.status, s.status AS "flags"
            FROM      ?_screenshots s
            LEFT JOIN ?_account a ON s.userIdOwner = a.id
            WHERE
                    { s.type = ?d}
                    { AND s.typeId = ?d}
                    { s.userIdOwner = ?d}
            LIMIT     100',
            $userId ? DBSIMPLE_SKIP : $type,
            $userId ? DBSIMPLE_SKIP : $typeId,
            $userId ? $userId : DBSIMPLE_SKIP
        );

        $num = [];
        foreach ($screenshots as $s)
        {
            if (empty($num[$s['type']][$s['typeId']]))
                $num[$s['type']][$s['typeId']] = 1;
            else
                $num[$s['type']][$s['typeId']]++;
        }

        // format data to meet requirements of the js
        foreach ($screenshots as $idx => &$s)
        {
            $s['date'] = date(Util::$dateFormatInternal, $s['date']);

            $s['name'] = "Screenshot #".$s['id'];           // what should we REALLY name it?

            if (isset($screenshots[$idx - 1]))
                $s['prev'] = $idx - 1;

            if (isset($screenshots[$idx + 1]))
                $s['next'] = $idx + 1;

            // order gives priority for 'status'
            if (!($s['flags'] & CC_FLAG_APPROVED))
            {
                $s['pending'] = 1;
                $s['status']  = 0;
            }
            else
                $s['status'] = 100;

            if ($s['flags'] & CC_FLAG_STICKY)
            {
                $s['sticky'] = 1;
                $s['status'] = 105;
            }

            if ($s['flags'] & CC_FLAG_DELETED)
            {
                $s['deleted'] = 1;
                $s['status'] = 999;
            }

            // something todo with massSelect .. am i doing this right?
            if ($num[$s['type']][$s['typeId']] == 1)
                $s['unique'] = 1;

            if (!$s['user'])
                unset($s['user']);
        }

        return $screenshots;
    }

    public static function getScreenshotPagesForManager($all, &$nFound)
    {
        // i GUESS .. ss_getALL ? everything : pending
        $nFound = 0;
        $pages  = DB::Aowow()->select('
             SELECT   s.`type`, s.`typeId`, count(1) AS "count", MIN(s.`date`) AS "date"
             FROM     ?_screenshots s
            {WHERE    (s.status & ?d) = 0}
             GROUP BY s.`type`, s.`typeId`',
            $all ? DBSIMPLE_SKIP : CC_FLAG_APPROVED | CC_FLAG_DELETED
        );

        if ($pages)
        {
            // limit to one actually existing type each
            $types = array_intersect(array_unique(array_column($pages, 'type')), array_keys(Util::$typeClasses));
            foreach ($types as $t)
            {
                $ids = [];
                foreach ($pages as $row)
                    if ($row['type'] == $t)
                        $ids[] = $row['typeId'];

                if (!$ids)
                    continue;

                $tClass = new Util::$typeClasses[$t](array(['id', $ids], CFG_SQL_LIMIT_NONE));
                foreach ($pages as &$p)
                    if ($p['type'] == $t)
                        if ($tClass->getEntry($p['typeId']))
                            $p['name'] = $tClass->getField('name', true);
            }

            foreach ($pages as &$p)
            {
                if (empty($p['name']))
                {
                    trigger_error('Screenshot linked to nonexistant type/typeId combination: '.$p['type'].'/'.$p['typeId'], E_USER_NOTICE);
                    unset($p);
                }
                else
                {
                    $nFound   += $p['count'];
                    $p['date'] = date(Util::$dateFormatInternal, $p['date']);
                }
            }
        }

        return $pages;
    }

    private static function getComments($type, $typeId)
    {

        $results  = DB::Aowow()->query(self::$commentQuery, User::$id, User::$id, 0, $type, $typeId, CC_FLAG_DELETED, User::$id, (int)User::isInGroup(U_GROUP_COMMENTS_MODERATOR));
        $comments = [];

        // additional informations
        $i = 0;
        foreach ($results as $r)
        {
            (new Markup($r['body']))->parseGlobalsFromText(self::$jsGlobals);

            self::$jsGlobals[TYPE_USER][$r['userId']] = $r['userId'];

            $c = array(
                'commentv2'  => 1,                          // always 1.. enables some features i guess..?
                'number'     => $i++,                       // some iterator .. unsued?
                'id'         => $r['id'],
                'date'       => date(Util::$dateFormatInternal, $r['date']),
                'roles'      => $r['roles'],
                'body'       => $r['body'],
                'rating'     => $r['rating'],
                'userRating' => $r['userRating'],
                'user'       => $r['user'],
            );

            $c['replies'] = self::getCommentReplies($r['id'], 5, $c['nreplies']);

            if ($r['responseBody'])                         // adminResponse
            {
                $c['response']      = $r['responseBody'];
                $c['responseroles'] = $r['responseRoles'];
                $c['responseuser']  = $r['responseUser'];

                (new Markup($r['responseBody']))->parseGlobalsFromText(self::$jsGlobals);
            }

            if ($r['editCount'])                            // lastEdit
                $c['lastEdit'] = [date(Util::$dateFormatInternal, $r['editDate']), $r['editCount'], $r['editUser']];

            if ($r['flags'] & CC_FLAG_STICKY)
                $c['sticky'] = true;

            if ($r['flags'] & CC_FLAG_DELETED)
            {
                $c['deleted']     = true;
                $c['deletedInfo'] = [date(Util::$dateFormatInternal, $r['deleteDate']), $r['deleteUser']];
            }

            if ($r['flags'] & CC_FLAG_OUTDATED)
                $c['outofdate'] = true;

            $comments[] = $c;
        }

        return $comments;
    }

    public static function getVideos($typeOrUser = 0, $typeId = 0, &$nFound = 0)
    {
        $videos = DB::Aowow()->selectPage($nFound, "
            SELECT v.id, a.displayName AS user, v.date, v.videoId, v.caption, IF(v.status & ?d, 1, 0) AS 'sticky', v.type, v.typeId
            FROM ?_videos v
            LEFT JOIN ?_account a ON v.userIdOwner = a.id
            WHERE {v.userIdOwner = ?d AND }{v.type = ? AND }{v.typeId = ? AND }v.status & ?d AND (v.status & ?d) = 0
            {ORDER BY ?# DESC}
            {LIMIT ?d}",
            CC_FLAG_STICKY,
            $typeOrUser < 0 ? -$typeOrUser         : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser         : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId             : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'               : DBSIMPLE_SKIP,
            !$typeOrUser    ? CFG_SQL_LIMIT_SEARCH : DBSIMPLE_SKIP
        );

        if ($typeOrUser <= 0)                               // not for search by type/typeId
        {
            foreach ($videos as $v)
                self::addSubject($v['type'], $v['typeId']);

            self::getSubjects();
        }

        // format data to meet requirements of the js
        foreach ($videos as &$v)
        {
            if ($typeOrUser <= 0)                           // not for search by type/typeId
            {
                if (!empty(self::$subjCache[$v['type']][$v['typeId']]) && !is_numeric(self::$subjCache[$v['type']][$v['typeId']]))
                    $v['subject'] = self::$subjCache[$v['type']][$v['typeId']];
                else
                    $v['subject'] = Lang::user('removed');
            }

            $v['date']      = date(Util::$dateFormatInternal, $v['date']);
            $v['videoType'] = 1;                            // always youtube

            if (!$v['sticky'])
                unset($v['sticky']);

            if (!$v['user'])
                unset($v['user']);
        }

        return $videos;
    }

    public static function getScreenshots($typeOrUser = 0, $typeId = 0, &$nFound = 0)
    {
        $screenshots = DB::Aowow()->selectPage($nFound, "
            SELECT s.id, a.displayName AS user, s.date, s.width, s.height, s.caption, IF(s.status & ?d, 1, 0) AS 'sticky', s.type, s.typeId
            FROM ?_screenshots s
            LEFT JOIN ?_account a ON s.userIdOwner = a.id
            WHERE {s.userIdOwner = ?d AND }{s.type = ? AND }{s.typeId = ? AND }s.status & ?d AND (s.status & ?d) = 0
            {ORDER BY ?# DESC}
            {LIMIT ?d}",
            CC_FLAG_STICKY,
            $typeOrUser < 0 ? -$typeOrUser         : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser         : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId             : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'               : DBSIMPLE_SKIP,
            !$typeOrUser    ? CFG_SQL_LIMIT_SEARCH : DBSIMPLE_SKIP
        );

        if ($typeOrUser <= 0)                               // not for search by type/typeId
        {
            foreach ($screenshots as $s)
                self::addSubject($s['type'], $s['typeId']);

            self::getSubjects();
        }

        // format data to meet requirements of the js
        foreach ($screenshots as &$s)
        {
            if ($typeOrUser <= 0)                           // not for search by type/typeId
            {
                if (!empty(self::$subjCache[$s['type']][$s['typeId']]) && !is_numeric(self::$subjCache[$s['type']][$s['typeId']]))
                    $s['subject'] = self::$subjCache[$s['type']][$s['typeId']];
                else
                    $s['subject'] = Lang::user('removed');
            }

            $s['date'] = date(Util::$dateFormatInternal, $s['date']);

            if (!$s['sticky'])
                unset($s['sticky']);

            if (!$s['user'])
                unset($s['user']);
        }

        return $screenshots;
    }

    public static function getAll($type, $typeId, &$jsg)
    {
        $result = array(
            'vi' => self::getVideos($type, $typeId),
            'sc' => self::getScreenshots($type, $typeId),
            'co' => self::getComments($type, $typeId)
        );

        Util::mergeJsGlobals($jsg, self::$jsGlobals);

        return $result;
    }
}
?>
