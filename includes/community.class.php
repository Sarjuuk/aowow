<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/************
* get Community Content
************/

/*  latest comments
		// $comments = array();
		// $rows = $DB->select('
			// SELECT `id`, `type`, `typeID`, LEFT(`commentbody`, 120) as `preview`, `userID` as `user`, `post_date` as `date`, (NOW()-`post_date`) as `elapsed`
			// FROM ?_comments
			// WHERE 1
			// ORDER BY post_date DESC
			// LIMIT 300
		// ');
		// foreach($rows as $i => $row)
		// {
			// $comments[$i] = array();
			// $comments[$i] = $row;
			// switch($row['type'])
			// {
				// case 1: // NPC
					// $comments[$i]['subject'] = $DB->selectCell('SELECT name FROM creature_template WHERE entry=?d LIMIT 1', $row['typeID']);
					// break;
				// case 2: // GO
					// $comments[$i]['subject'] = $DB->selectCell('SELECT name FROM gameobject_template WHERE entry=?d LIMIT 1', $row['typeID']);
					// break;
				// case 3: // Item
					// $comments[$i]['subject'] = $DB->selectCell('SELECT name FROM item_template WHERE entry=?d LIMIT 1', $row['typeID']);
					// break;
				// case 4: // Item Set
					// $comments[$i]['subject'] = $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_itemset WHERE Id=?d LIMIT 1', $row['typeID']);
					// break;
				// case 5: // Quest
					// $comments[$i]['subject'] = $DB->selectCell('SELECT Title FROM quest_template WHERE entry=?d LIMIT 1', $row['typeID']);
					// break;
				// case 6: // Spell
					// $comments[$i]['subject'] = $DB->selectCell('SELECT spellname_loc'.$_SESSION['locale'].' FROM ?_spell WHERE spellID=?d LIMIT 1', $row['typeID']);
					// break;
				// case 7: // Zone
					// // TODO
					// break;
				// case 8: // Faction
					// $comments[$i]['subject'] = $DB->selectCell('SELECT name_loc'.$_SESSION['locale'].' FROM ?_factions WHERE factionID=?d LIMIT 1', $row['typeID']);
					// break;
				// default:
					// $comments[$i]['subject'] = 'Unknown';
					// break;;
			// }
			// $comments[$i]['user'] = $rDB->selectCell('SELECT CONCAT(UCASE(SUBSTRING(username, 1,1)),LOWER(SUBSTRING(username, 2))) FROM aowow_account WHERE id=?d LIMIT 1', $row['user']);
			// if(empty($comments[$i]['user']))
				// $comments[$i]['user'] = 'Anonymous';
			// $comments[$i]['rating'] = array_sum($DB->selectCol('SELECT rate FROM ?_comments_rates WHERE commentid=?d', $row['id']));
			// $comments[$i]['purged'] = ($comments[$i]['rating'] <= -50)? 1: 0;
			// $comments[$i]['deleted'] = 0;
		// }
		// $smarty->assign('comments', $comments);
*/

/* yet another todo (aug. 2010)
    extend g_users with authors
    _['ArgentSun']={border:1,roles:140,joined:'2007/11/17 17:21:48',posts:5575,title:'The Ambitious',avatar:2,avatarmore:'395',             sig:'[i] ‎"Schrödinger\'s cat walks into a bar...\n... and it doesn\'t!"[/i]'};
    _['Fearow']=   {         roles:0,  joined:'2009/12/25 08:36:58',posts:3,                         avatar:1,avatarmore:'inv_misc_herb_17',sig:'But if your life is such a big joke, then why should I care?'};
*/
/*
    {id:115,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Guide to the Fury Warrior (Part 1)',videoType:1,videoId:'VUvxFvVmttg',type:13,typeId:1},
    {id:116,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Guide to the Fury Warrior (Part 2)',videoType:1,videoId:'VEfnuIcq7n8',type:13,typeId:1},
    {id:117,user:'Ciderhelm',date:'2010/05/10 19:14:18',caption:'TankSpot\'s Protection Warrior Guide',videoType:1,videoId:'vF-7kmvJZXY',type:13,typeId:1,sticky:1}
*/

class CommunityContent
{
    /* todo: administration of content */

    private function getComments($type, $typeId)
    {
        // comments
        return [];
    }

    private function getVideos($type, $typeId)
    {
        return DB::Aowow()->Query("
            SELECT
                v.id,
                a.displayName AS user,
                v.date,
                v.videoId,
                v.caption,
                IF(v.status & 0x4, 1, 0) AS 'sticky'
            FROM
                ?_videos v,
                ?_account a
            WHERE
                v.type = ? AND v.typeId = ? AND v.status & 0x2 AND v.uploader = a.id",
            $type,
            $typeId
        );
    }

    private function getScreenshots($type, $typeId)
    {
        return DB::Aowow()->Query("
            SELECT
                s.id,
                a.displayName AS user,
                s.date,
                s.width,
                s.height,
                s.caption,
                IF(s.status & 0x4, 1, 0) AS 'sticky'
            FROM
                ?_screenshots s,
                ?_account a
            WHERE
                s.type = ? AND s.typeId = ? AND s.status & 0x2 AND s.uploader = a.id",
            $type,
            $typeId
        );
    }

    public function getAll($type, $typeId)
    {
        return array(
            'vi' => self::getVideos($type, $typeId),
            'sc' => self::getScreenshots($type, $typeId),
            'co' => self::getComments($type, $typeId)
        );
    }
}
?>
