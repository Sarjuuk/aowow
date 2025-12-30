<?php

namespace Aowow;

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
    public const /* int */ COMMENT_LENGTH_MIN = 10;
    public const /* int */ COMMENT_LENGTH_MAX = 7500;
    public const /* int */ REPLY_LENGTH_MIN   = 15;
    public const /* int */ REPLY_LENGTH_MAX   = 600;

    public const /* int */ REPORT_THRESHOLD_AUTO_DELETE      = 10;
    public const /* int */ REPORT_THRESHOLD_AUTO_OUT_OF_DATE = 5;

    private static array $jsGlobals = [];
    private static array $subjCache = [];

    private static string $coCountQuery =
       'SELECT    COUNT(1)
        FROM      ?_comments c
        WHERE     c.`replyTo` = ?d AND c.`type` = ?d AND c.`typeId` = ?d AND
                  ((c.`flags` & ?d) = 0 OR c.`userId` = ?d OR ?d)';

    private static string $coQuery =
       'SELECT    c.*,
                  a1.`username` AS "user",
                  a2.`username` AS "editUser",
                  a3.`username` AS "deleteUser",
                  a4.`username` AS "responseUser",
                  IFNULL(SUM(ur.`value`), 0) AS "rating",
                  SUM(IF(ur.`userId` > 0 AND ur.`userId` = ?d, ur.`value`, 0)) AS "userRating",
                  IF(r.`id` IS NULL, 0, 1) AS "userReported"
        FROM      ?_comments c
        JOIN      ?_account a1 ON c.`userId` = a1.`id`
        LEFT JOIN ?_account a2 ON c.`editUserId` = a2.`id`
        LEFT JOIN ?_account a3 ON c.`deleteUserId` = a3.`id`
        LEFT JOIN ?_account a4 ON c.`responseUserId` = a4.`id`
        LEFT JOIN ?_user_ratings ur ON c.`id` = ur.`entry` AND ur.`type` = ?d
        LEFT JOIN ?_reports r ON r.`subject` = c.`id` AND r.`mode` = ?d AND r.`userId` = ?d
        WHERE     c.`replyTo` = ?d AND c.`type` = ?d AND c.`typeId` = ?d AND
                  ((c.`flags` & ?d) = 0 OR c.`userId` = ?d OR ?d)
        GROUP BY  c.`id`
        ORDER BY  c.`date` ASC';

    private static string $ssQuery =
       'SELECT    s.`id` AS ARRAY_KEY, s.`id`, a.`username` AS "user", s.`date`, s.`width`, s.`height`, s.`caption`, IF(s.`status` & ?d, 1, 0) AS "sticky", s.`type`, s.`typeId`
        FROM      ?_screenshots s
        LEFT JOIN ?_account a ON s.`userIdOwner` = a.`id`
        WHERE   { s.`userIdOwner` = ?d AND }{ s.`type` = ? AND }{ s.`typeId` = ? AND } s.`status` & ?d AND (s.`status` & ?d) = 0
      { ORDER BY  ?# DESC }
      { LIMIT     ?d }';

    private static string $viQuery =
       'SELECT    v.`id` AS ARRAY_KEY, v.`id`, a.`username` AS "user", v.`date`, v.`videoId`, v.`caption`, IF(v.`status` & ?d, 1, 0) AS "sticky", v.`type`, v.`typeId`
        FROM      ?_videos v
        LEFT JOIN ?_account a ON v.`userIdOwner` = a.`id`
        WHERE   { v.`userIdOwner` = ?d AND }{ v.`type` = ? AND }{ v.`typeId` = ? AND } v.`status` & ?d AND (v.`status` & ?d) = 0
      { ORDER BY  ?# ASC }
      { LIMIT     ?d }';

    private static string $previewQuery =
       'SELECT    c.`id`,
                  c.`body` AS "preview",
                  c.`date`,
                  c.`replyTo` AS "commentid",
                  IF(c.`flags` & ?d, 1, 0) AS "deleted",
                  IF(c.`type` <> 0, c.`type`, c2.`type`) AS "type",
                  IF(c.`typeId` <> 0, c.`typeId`, c2.`typeId`) AS "typeId",
                  IFNULL(SUM(ur.`value`), 0) AS "rating",
                  a.`username` AS "user"
        FROM      ?_comments c
        JOIN      ?_account a ON c.`userId` = a.`id`
        LEFT JOIN ?_user_ratings ur ON ur.`entry` = c.`id` AND ur.`userId` <> 0 AND ur.`type` = 1
        LEFT JOIN ?_comments c2 ON c.`replyTo` = c2.`id`
        WHERE     %s
                  ((c.`flags` & ?d) = 0 OR c.`userId` = ?d OR ?d)
        GROUP BY  c.`id`
        ORDER BY  c.`date` DESC
      { LIMIT     ?d }';

    private static function addSubject(int $type, int $typeId) : void
    {
        if (!isset(self::$subjCache[$type][$typeId]))
            self::$subjCache[$type][$typeId] = 0;
    }

    private static function getSubjects() : void
    {
        foreach (self::$subjCache as $type => $ids)
        {
            $_ = array_filter(array_keys($ids), 'is_numeric');
            if (!$_)
                continue;

            $obj = Type::newList($type, [['id', $_]]);
            if (!$obj)
                continue;

            foreach ($obj->iterate() as $id => $__)
                self::$subjCache[$type][$id] = $obj->getField('name', true, true);
        }
    }

    public static function getCommentPreviews(array $opt = [], ?int &$nFound = 0, bool $dateFmt = true, int $resultLimit = 0) : array
    {
        /*
            purged:0,           <- doesnt seem to be used anymore
            domain:'live'       <- irrelevant for our case
        */

        // add default values
        $opt += ['user' => 0, 'unrated' => 0, 'comments' => 0, 'replies' => 0, 'flags' => 0];

        $w = [];
        if ($opt['user'])
            $w[] = sprintf('c.`userId` = %d AND', $opt['user']);
        if ($opt['unrated'])
            $w[] = 'ur.`entry` IS NULL AND';
        if ($opt['flags'])
            $w[] = sprintf('(c.`flags` & %d) > 0 AND', $opt['flags']);
        if ($opt['comments'] && !$opt['replies'])
            $w[] = 'c.`replyTo` = 0 AND';
        else if (!$opt['comments'] && $opt['replies'])
            $w[] = 'c.`replyTo` <> 0 AND';
     // else
     //     pick both and no extra constraint needed for that

        $query = sprintf(self::$previewQuery, implode(' ', $w));

        $comments = DB::Aowow()->select(
            $query,
            CC_FLAG_DELETED,
            CC_FLAG_DELETED,
            User::$id,
            User::isInGroup(U_GROUP_COMMENTS_MODERATOR),
            $resultLimit ?: DBSIMPLE_SKIP
        );

        if (!$comments)
            return [];

        $nFound = DB::Aowow()->selectCell(
            substr_replace($query, 'SELECT COUNT(*) ', 0, strpos($query, 'FROM')),
            CC_FLAG_DELETED,
            User::$id,
            User::isInGroup(U_GROUP_COMMENTS_MODERATOR),
            DBSIMPLE_SKIP
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
                $c['elapsed'] = time() - $c['date'];
                $c['date']    = $dateFmt ? date(Util::$dateFormatInternal, $c['date']) : intVal($c['date']);

                // remove commentid if not looking for replies
                if (empty($opt['replies']))
                    unset($c['commentid']);

                // format text for listview
                $c['preview'] = Lang::trimTextClean($c['preview']);
            }
            else
            {
                trigger_error('Comment '.$c['id'].' belongs to nonexistent subject.', E_USER_NOTICE);
                unset($comments[$idx]);
            }
        }

        return array_values($comments);
    }

    public static function getCommentReplies(int $commentId, int $limit = 0, ?int &$nFound = 0) : array
    {
        $replies = [];
        $query = $limit > 0 ? self::$coQuery.' LIMIT '.$limit : self::$coQuery;

        // get replies
        if ($results = DB::Aowow()->select($query, User::$id, RATING_COMMENT, Report::MODE_COMMENT, User::$id, $commentId, 0, 0, CC_FLAG_DELETED, User::$id, User::isInGroup(U_GROUP_COMMENTS_MODERATOR)))
        {
            $nFound = DB::Aowow()->selectCell(self::$coCountQuery, $commentId, 0, 0, CC_FLAG_DELETED, User::$id, User::isInGroup(U_GROUP_COMMENTS_MODERATOR));

            foreach ($results as $r)
            {
                Markup::parseTags($r['body'], self::$jsGlobals);

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
        }

        return $replies;
    }

    public static function getComments(int $type, int $typeId) : array
    {

        $results  = DB::Aowow()->query(self::$coQuery, User::$id, RATING_COMMENT, Report::MODE_COMMENT, User::$id, 0, $type, $typeId, CC_FLAG_DELETED, User::$id, (int)User::isInGroup(U_GROUP_COMMENTS_MODERATOR));
        $comments = [];

        // additional informations
        $i = 0;
        foreach ($results as $r)
        {
            Markup::parseTags($r['body'], self::$jsGlobals);

            self::$jsGlobals[Type::USER][$r['userId']] = $r['userId'];

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
                'nreplies'   => 0
            );

            $c['replies'] = self::getCommentReplies($r['id'], 5, $c['nreplies']);

            if ($r['responseBody'])                         // adminResponse
            {
                $c['response']      = $r['responseBody'];
                $c['responseroles'] = $r['responseRoles'];
                $c['responseuser']  = $r['responseUser'];

                Markup::parseTags($r['responseBody'], self::$jsGlobals);
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

    public static function getVideos(int $typeOrUser = 0, int $typeId = 0, ?int &$nFound = 0, bool $dateFmt = true, int $resultLimit = 0) : array
    {
        $videos = DB::Aowow()->select(self::$viQuery,
            CC_FLAG_STICKY,
            $typeOrUser < 0 ? -$typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId     : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'       : 'pos',
            $resultLimit ?: DBSIMPLE_SKIP
        );

        if (!$videos)
            return [];

        $nFound = DB::Aowow()->selectCell(
            substr_replace(self::$viQuery, 'SELECT COUNT(*) ', 0, strpos(self::$viQuery, 'FROM')),
            $typeOrUser < 0 ? -$typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId     : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'       : 'pos',
            DBSIMPLE_SKIP
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

            $v['date']      = $dateFmt ? date(Util::$dateFormatInternal, $v['date']) : intVal($v['date']);
            $v['videoType'] = 1;                            // always youtube

            if (!$v['sticky'])
                unset($v['sticky']);

            if (!$v['user'])
                unset($v['user']);
        }

        return array_values($videos);
    }

    public static function getScreenshots(int $typeOrUser = 0, int $typeId = 0, ?int &$nFound = 0, bool $dateFmt = true, int $resultLimit = 0) : array
    {
        $screenshots = DB::Aowow()->select(self::$ssQuery,
            CC_FLAG_STICKY,
            $typeOrUser < 0 ? -$typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId     : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'       : DBSIMPLE_SKIP,
            $resultLimit ?: DBSIMPLE_SKIP
        );

        if (!$screenshots)
            return [];

        $nFound = DB::Aowow()->selectCell(
            substr_replace(self::$ssQuery, 'SELECT COUNT(*) ', 0, strpos(self::$ssQuery, 'FROM')),
            $typeOrUser < 0 ? -$typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeOrUser : DBSIMPLE_SKIP,
            $typeOrUser > 0 ?  $typeId     : DBSIMPLE_SKIP,
            CC_FLAG_APPROVED,
            CC_FLAG_DELETED,
            !$typeOrUser    ? 'date'       : DBSIMPLE_SKIP,
            DBSIMPLE_SKIP
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

            $s['date'] = $dateFmt ? date(Util::$dateFormatInternal, $s['date']) : intVal($s['date']);

            if (!$s['sticky'])
                unset($s['sticky']);

            if (!$s['user'])
                unset($s['user']);
        }

        return array_values($screenshots);
    }

    public static function getJSGlobals() : array
    {
        return self::$jsGlobals;
    }
}
?>
