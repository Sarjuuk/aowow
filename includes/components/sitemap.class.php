<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('invalid access');


// assumptions
// every character in this sitemap will be bog-standard ANSI
// so it consumes 1 Byte in UTF-8
// every item is thus <140 byte
// so we hit 50k entries and have ~3.5MB storage capacity left

class Sitemap
{
    public const /* string */ ERR_TITLE  = 'Invalid sitemap';
    public const /* string */ ERR_PAGE   = 'This sitemap does not exist.';
    public const /* string */ ERR_OFFSET = 'The maximum page for this sitemap is %d.';

    private const /* int */ MAX_ENTRIES  = 50000;
    private const /* int */ LASTMOD_BASE = 1435701600;      // 01.07.2015 - 00:00:00

    public static int $maxPage = 0;

    private static string $page       = '';
    private static int    $offset     = 1;
    private static array  $validPages = array(
        'npc'         => [Type::NPC,         '::creature',        'IF(x.`cuFlags` & 0x40000000, 0.1, 0.4)'],
        'object'      => [Type::OBJECT,      '::objects',         'IF(x.`cuFlags` & 0x40000000, 0.1, 0.4)'],
        'item'        => [Type::ITEM,        '::items',           'IF(x.`cuFlags` & 0x40000000, 0.1, IF(src.`typeId` IS NULL, 0.5, 0.7))'],
        'itemset'     => [Type::ITEMSET,     '::itemset',         'IF(x.`cuFlags` & 0x40000000, 0.1, 0.7)'],
        'quest'       => [Type::QUEST,       '::quests',          'IF(x.`cuFlags` & 0x40000000, 0.1, IF(src.`typeId` IS NULL, 0.3, 0.5))'],
        'spell'       => [Type::SPELL,       '::spell',           'IF(x.`cuFlags` & 0x40000000, 0.1, IF(src.`typeId` IS NULL, 0.5, 0.8))'],
        'zone'        => [Type::ZONE,        '::zones',           'IF(x.`cuFlags` & 0x40000000, 0.1, 0.4)'],
        'faction'     => [Type::FACTION,     '::factions',        'IF(x.`cuFlags` & 0x40000000, 0.1, 0.4)'],
        'pet'         => [Type::PET,         '::pet',             'IF(x.`cuFlags` & 0x40000000, 0.1, 0.4)'],
        'achievement' => [Type::ACHIEVEMENT, '::achievement',     'IF(x.`cuFlags` & 0x40000000, 0.1, IF(x.`category` = 81, 0.6, IF(x.`category` IN (1, 122, 133, 141, 134, 14807, 131, 130, 128, 132, 21, 124, 135, 126, 154, 125, 140, 145, 147, 136, 127, 152, 153, 191, 123, 14822, 14821, 14823, 137, 178, 173, 14963, 15021, 15062), 0.3, 0.4)))'],
        'title'       => [Type::TITLE,       '::titles',          'IF(x.`cuFlags` & 0x40000000, 0.1, IF(src.`typeId` IS NULL, 0.3, 0.4))'],
        'event'       => [Type::WORLDEVENT,  '::events',          'IF(x.`cuFlags` & 0x40000000, 0.1, IF(x.`holidayId` = 0, 0.2, 0.4))'],
        'class'       => [Type::CHR_CLASS,   '::classes',         'IF(x.`cuFlags` & 0x40000000, 0.1, 0.7)'],
        'race'        => [Type::CHR_RACE,    '::races',           'IF(x.`cuFlags` & 0x40000000, 0.1, 0.7)'],
        'skill'       => [Type::SKILL,       '::skillline',       'IF(x.`cuFlags` & 0x40000000, 0.1, IF(x.`typeCat` IN(11, 9), 0.5, IF(x.`typeCat` IN (8, 6), 0.4, 0.3)))'],
        'currency'    => [Type::CURRENCY,    '::currencies',      'IF(x.`cuFlags` & 0x40000000, 0.1, IF(x.`category` = 3, 0.2, IF(x.`description_loc0`, 0.4, 0.3)))'],
        'sound'       => [Type::SOUND,       '::sounds',          'IF(x.`cuFlags` & 0x40000000, 0.1, 0.3)'],
        'icons'       => [Type::ICON,        '::icons',           'IF(x.`cuFlags` & 0x40000000, 0.1, 0.3)'],
        'emote'       => [Type::EMOTE,       '::emotes',          'IF(x.`cuFlags` & 0x40000000, 0.1, 0.3)'],
        'enchantment' => [Type::ENCHANTMENT, '::itemenchantment', 'IF(x.`cuFlags` & 0x40000000, 0.1, IF(x.`type1` IN (1, 7) OR x.`type2` IN (1, 7) OR x.`type3` IN (1, 7), 0.4, 0.3))'],
        'areatrigger' => [Type::AREATRIGGER, '::areatrigger',     'IF(x.`cuFlags` & 0x40000000, 0.1, 0.3)'],
        'mail'        => [Type::MAIL,        '::mails',           'IF(x.`cuFlags` & 0x40000000, 0.1, 0.3)']
     // 'guide'       => [Type::GUIDE,       '::guides',          ''] super low prio .. need a way to filter for publicly visible guides
    );

    public static function generate(string $page, int $offset) : ?string
    {
        self::$page   = $page;
        self::$offset = $offset;

        if (!self::$page)
            return self::getIndex();
        else if (self::$page == 'special')
            return self::getSpecial();
        else if (isset(self::$validPages[self::$page][1]))
            return self::getPage();

        // whoops!
        return null;
    }

    private static function getIndex() : ?string
    {
        $root = new SimpleXML('<sitemapindex />');
        $root->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $root->addChild('sitemap')->addChild('loc', Cfg::get('HOST_URL').'/?sitemap=special');

        foreach (self::$validPages as $page => [, $table, ])
        {
            $n = DB::Aowow()->selectCell('SELECT CEIL(COUNT(*) / %i) FROM %n', self::MAX_ENTRIES, $table);
            for ($i = 1; $i <= $n; $i++)
                $root->addChild('sitemap')->addChild('loc', Cfg::get('HOST_URL').'/?sitemap='.$page.'&amp;page='.$i);
        }

        return $root->asXML() ?: null;
    }

    private static function getSpecial() : ?string
    {
        if (self::$offset != 1)
        {
            self::$maxPage = 1;
            return null;
        }

        $root = new SimpleXML('<urlset />');
        $root->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        // home
        $url = $root->addChild('url');
        $url->addChild('loc', Cfg::get('HOST_URL'));
        $url->addChild('priority', 1);
        $url->addChild('changefreq', 'monthly');

        // talent calc
        $url = $root->addChild('url');
        $url->addChild('loc', Cfg::get('HOST_URL').'/?talent');
        $url->addChild('priority', 1);
        $url->addChild('changefreq', 'yearly');

        // pet calc
        $url = $root->addChild('url');
        $url->addChild('loc', Cfg::get('HOST_URL').'/?petcalc');
        $url->addChild('priority', 0.8);
        $url->addChild('changefreq', 'yearly');

        // item compare
        $url = $root->addChild('url');
        $url->addChild('loc', Cfg::get('HOST_URL').'/?compare');
        $url->addChild('priority', 0.9);
        $url->addChild('changefreq', 'yearly');

        // profiler
        if (Cfg::get('PROFILER_ENABLE'))
        {
            $url = $root->addChild('url');
            $url->addChild('loc', Cfg::get('HOST_URL').'/?profiler');
            $url->addChild('priority', 1);
            $url->addChild('changefreq', 'yearly');
        }

        // maps
        $url = $root->addChild('url');
        $url->addChild('loc', Cfg::get('HOST_URL').'/?maps');
        $url->addChild('priority', 0.7);
        $url->addChild('changefreq', 'yearly');

        return $root->asXML();
    }

    private static function getPage() : ?string
    {
        [$type, $table, $prioString] = self::$validPages[self::$page];

        $n = DB::Aowow()->selectCell('SELECT CEIL(COUNT(*) / %i) FROM %n', self::MAX_ENTRIES, $table);
        if (self::$offset <= 0 || self::$offset > $n)
        {
            self::$maxPage = $n;
            return null;
        }

        $root = new SimpleXML('<urlset />');
        $root->addAttribute('xmlns', 'http://www.sitemaps.org/schemas/sitemap/0.9');

        $rows = DB::Aowow()->selectAssoc(
           'SELECT x.`id` AS ARRAY_KEY, ('.$prioString.') AS "priority", GREATEST(IFNULL(MAX(ss.`date`), 0), IFNULL(MAX(vi.`date`), 0), IFNULL(MAX(co.`date`), 0)) AS "lastmod" FROM %n x
            LEFT JOIN ::source      src ON src.`type` = %i AND src.`typeId` = x.`id`
            LEFT JOIN ::comments    co  ON  co.`type` = %i AND  co.`typeId` = x.`id` AND (co.`flags` & %i) = 0
            LEFT JOIN ::screenshots ss  ON  ss.`type` = %i AND  ss.`typeId` = x.`id` AND (co.`flags` & %i) = 0 AND (co.`flags` & %i) > 0
            LEFT JOIN ::videos      vi  ON  vi.`type` = %i AND  vi.`typeId` = x.`id` AND (co.`flags` & %i) = 0 AND (co.`flags` & %i) > 0
            GROUP BY x.`id` LIMIT %i, %i',
            $table,
            $type,
            $type, CC_FLAG_DELETED,
            $type, CC_FLAG_DELETED, CC_FLAG_APPROVED,
            $type, CC_FLAG_DELETED, CC_FLAG_APPROVED,
            self::MAX_ENTRIES * (self::$offset - 1), self::MAX_ENTRIES
        );

        foreach ($rows as $id => $pair)
        {
            $url = $root->addChild('url');
            $url->addChild('loc', Cfg::get('HOST_URL').'/?'.self::$page.'='.$id);
            $url->addChild('priority', $pair['priority']);
            $url->addChild('lastmod', date('c', $pair['lastmod'] ?: self::LASTMOD_BASE));
        }

        return $root->asXML();
    }
}

?>
