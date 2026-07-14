<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/**
 * collection of functions to handle various FrameXML excentricities
 */
final class UIText
{
    private const /* array */ LINE_BREAK = array(
        -1               => ' ',
        Lang::FMT_RAW    => "\n",
        Lang::FMT_HTML   => '<br />',
        Lang::FMT_MARKUP => '[br]'
    );

    private const /* array */ VALID_TAGS = array(
        'h1', 'h2', 'h3', 'p', 'a', 'img', 'span', 'br'     // also html + body but they are handled separately
    );

    /**
     * shorthand of UIText::format($txt, Lang::FMT_HTML) for use as LocString formatter
     * @param string    $text   text to format
     * @return string formatted text
     */
    public static function formatHtml(string $text) : string
    {
        return self::format($text, Lang::FMT_HTML);
    }

    /**
     * format game text for display in browser
     *
     * @param string    $text   text to format
     * @param int       $fmt    target output format
     * * `-1` - mixed format (historic)
     * * `Lang::FMT_RAW` - display unformatted
     * * `Lang::FMT_HTML` - html formatted
     * * `Lang::FMT_MARKUP` - formatted for use in JS Markup class
     * @return string formatted text
     */
    public static function format(string $text, int $fmt = -1) : string
    {
        if (!isset(self::LINE_BREAK[$fmt]))
            $fmt = -1;

        // this always happens
        $text = strtr($text, ["\r" => '']);

        if (!$text)
            return '';

        if (strpos($text, '|') !== false)
            $text = self::unescapeUISequences($text, $fmt);

        if (strpos($text, '$') !== false)
            $text = self::replaceTextVariables($text, $fmt);

        if (stripos($text, '<HTML>') !== false)
            $text = self::handleSimpleHtml($text, $fmt);
        else
            $text = strtr($text, ["\n" => self::LINE_BREAK[$fmt]]);

        // if target output is html escape fake html-ish tags the browser skipsh dishplaying ...<hic>!
        $text = preg_replace_callback('/<\/?([a-z1-3]+)(?:[^>]*)>/i', function ($m) use ($fmt) {
            [$full, $tag] = $m;

            if (in_array(strtolower($tag), self::VALID_TAGS))
                return $fmt == Lang::FMT_MARKUP ? '['.substr($full, 1, -1).']' : $full;

            if ($fmt == Lang::FMT_HTML)
                return '&lt;'.substr($full, 1, -1).'&gt;';

            return $full;
        }, $text);

        return $text;
    }

    /**
     * https://warcraft.wiki.gg/wiki/UIOBJECT_SimpleHTML
     *
     * Only referenced by ItemTextFrame.xml as it creates ItemTextPageText.
     * Mails committed to item are also displayed in ItemTextFrame but neither MailTemplate.dbc nor TDBs achievement_reward make use of SimpleHTML.
     *
     * @param string    $text   text to format
     * @param int       $fmt    target output format
     * * `-1` - mixed format (historic)
     * * `Lang::FMT_RAW` - display unformatted
     * * `Lang::FMT_HTML` - html formatted
     * * `Lang::FMT_MARKUP` - formatted for use in JS Markup class
     * @return string formatted text
     */
    private static function handleSimpleHtml(string $text, int $fmt = Lang::FMT_HTML) : string
    {
        $text = str_ireplace(
            ['<HTML>', '</HTML>', '<BODY>', '</BODY>', '<BR>',                  '</BR>'],
            ['',       '',        '',       '',         self::LINE_BREAK[$fmt], ''     ],
            $text
        );

        // redirect 'src' of <IMG> tags
        $text = preg_replace_callback('/src="([^"]+)"/i', fn($m) => sprintf('src="%s/images/wow/%s.png"', Cfg::get('STATIC_URL'), strtr($m[1], ['\\' => '/'])), $text);

        // docs say SimpleHTML supports anchors though in 335 they seem to be unused
        // also, where the hell do they link to?
        $text = preg_replace('/<a href="[^"]*">([^<]*)<\/a>/ui', '\1', $text);

        return $text;
    }

    /**
     * for `$`-prefix variables. Most frames bar tooltips seem to accept these
     * * `$g` gender
     * * `$t` pvp rank
     * * `$w` world state
     * * `$c` player class
     * * `$r` player race
     * * `$n` player name
     * * `$b` line break
     *
     * @param string    $text   text to format
     * @param int       $fmt    target output format
     * * `-1` - mixed format (historic)
     * * `Lang::FMT_RAW` - display unformatted
     * * `Lang::FMT_HTML` - html formatted
     * * `Lang::FMT_MARKUP` - formatted for use in JS Markup class
     * @return string formatted text
     */
    private static function replaceTextVariables(string $text, int $fmt = Lang::FMT_HTML) : string
    {
        $from = array(
            '/\$g *([^:;]*) *: *([^:;]*) *(:?[^:;]*);/ui',  // $g<male>:<female>:<refVariable>
            '/\$t([^;]+);/ui',                              // $t<male>:<female>; (maybe male/female if pvp unranked? Gets replaced with current HK rank.)
            '/\$(\d+)w/ui',                                 // $<id>w
            '/\$c/i',
            '/\$r/i',
            '/\$n/i',
            '/\$b/i'
        );

        $to[Lang::FMT_MARKUP] = array(
            '<\1/\2>',
            '<'.implode('/', Lang::game('pvpRank', 1)).'>',
            '[span class=q0>WorldState #\1[/span]',
            '<'.Lang::game('class').'>',
            '<'.Lang::game('race').'>',
            '<'.Lang::main('name').'>',
            '[br]'
        );

        $to[Lang::FMT_HTML] = array(
            '&lt;\1/\2&gt;',
            '&lt;'.implode('/', Lang::game('pvpRank', 1)).'&gt;',
            '<span class="q0">WorldState #\1</span>',
            '&lt;'.Lang::game('class').'&gt;',
            '&lt;'.Lang::game('race').'&gt;',
            '&lt;'.Lang::main('name').'&gt;',
            '<br />'
        );

        $to[Lang::FMT_RAW] =
        $to[-1] = array(
            '<\1/\2>',
            '<'.implode('/', Lang::game('pvpRank', 1)).'>',
            'WorldState #\1',
            '<'.Lang::game('class').'>',
            '<'.Lang::game('race').'>',
            '<'.Lang::main('name').'>',
            "\n"
        );

        return preg_replace($from, $to[$fmt], $text);
    }

    /**
     * https://warcraft.wiki.gg/wiki/UI_escape_sequences
     *
     * UI escape sequences are understood by basically every part of the games ui
     *
     * @param string    $text   text to format
     * @param int       $fmt    target output format
     * * `-1` - mixed format (historic)
     * * `Lang::FMT_RAW` - display unformatted
     * * `Lang::FMT_HTML` - html formatted
     * * `Lang::FMT_MARKUP` - formatted for use in JS Markup class
     * @return string formatted text
     */
    public static function unescapeUISequences(?string $text, int $fmt = -1) : string
    {
        if (!$text)
            return '';

        if (strpos($text, '|') === false)
            return $text;

        // line break                    |n
        $text = preg_replace_callback('/\|n/i', function ($m) use ($fmt)
            {
                switch ($fmt)
                {
                    case -1:                                // default Lang::vspf case
                    case Lang::FMT_HTML:
                        return '<br />';
                    case Lang::FMT_MARKUP:
                        return '[br]';
                    case Lang::FMT_RAW:
                    default:
                        return '';
                }
            }, $text);

        // color                         |c<aarrggbb><word>|r
        $text = preg_replace_callback('/\|c([[:xdigit:]]{2})([[:xdigit:]]{6})(.+?)\|r/is', function ($m) use ($fmt)
            {
                [$_, $a, $rgb, $text] = $m;

                switch ($fmt)
                {
                    case -1:                                // default Lang::vspf case
                    case Lang::FMT_HTML:
                        return sprintf('<span style="color: #%1$s%2$s;">%3$s</span>', $rgb, $a, $text);
                    case Lang::FMT_MARKUP:
                        return sprintf('[span color=#%1$s]%3$s[/span]', $rgb, $a, $text); // doesn't support alpha
                    case Lang::FMT_RAW:
                    default:
                        return $text;
                }
            }, $text);

        // icon                          |T<imgPath+File.blp>:0:0:0:-1|t
        $text = preg_replace_callback('/\|T([\w]+\\\)*([^\.:]+)(?:\.blp)?:([^\|]+)\|t/i', function ($m) use ($fmt)
            {
                /* iconParam - size1, size2, xoffset, yoffset
                    size1 == 0; size2 omitted: Width = Height = TextHeight (always square!)
                    size1 > 0;  size2 omitted: Width = Height = size1 (always square!)
                    size1 == 0; size2 == 0   : Width = Height = TextHeight (always square!)
                    size1 > 0;  size2 == 0   : Width = TextHeight; Height = size1 (size1 is height!!!)
                    size1 == 0; size2 > 0    : Width = size2 * TextHeight; Height = TextHeight (size2 is an aspect ratio and defines width!!!)
                    size1 > 0;  size2 > 0    : Width = size1; Height = size2
                */

                [$_, $iconPath, $iconName, $iconParam] = $m;

                switch ($fmt)
                {
                    case Lang::FMT_HTML:
                        return '<span class="icontiny" style="background-image: url('.Cfg::get('STATIC_URL').'/images/wow/icons/tiny/'.Util::lower($iconName).'.gif)">';
                    case Lang::FMT_MARKUP:
                        return '[icon name='.Util::lower($iconName).']';
                    case Lang::FMT_RAW:
                    default:
                        return '';
                }
            }, $text);

        // hyperlink                     |H<hyperlinkStruct>|h<name>|h
        $text = preg_replace_callback('/\|H([^:]+):([^\|]+)\|h([^\|]+)\|h/i', function ($m) use ($fmt)
            {
                /*  type            Params
                    |Hchannel       channelName, channelname == CHANNEL ? channelNr : null
                    |Hachievement   AchievementID, PlayerGUID, isComplete, Month, Day, Year, criteriaMask1, criteriaMask2, criteriaMask3, criteriaMask4 - 32bit masks of Achievement_criteria.dbc/UIOrder only for achievements that display a todo list
                    |Hquest         QuestID, QuestLevel
                    |Hitem          itemId enchantId gemId1 gemId2 gemId3 gemId4 suffixId uniqueId linkLevel
                    |Henchant       SpellID (from craftwindow)
                    |Htalent        TalentID, TalentRank
                    |Hspell         SpellID, PlayerLevel?
                    |Htrade         SpellID, curSkill, maxSkill, PlayerGUID, base64_encode(known recipes bitmask)
                    |Hplayer        Name
                    |Hunit          GUID    ?               -  combatlog
                    |Hicon          ?   "source"|"dest"     -  combatlog
                    |Haction        ?                       -  combatlog
                */

                [$_, $linkType, $linkVars, $text] = $m;

                $linkVars = explode(':', $linkVars);

                $spfVars = ['', $linkVars[0], $text];

                switch ($linkType)
                {
                    case 'trade':
                    case 'enchant':
                        $linkType = 'spell';
                    case 'achievement':                             // markdown COULD implement completed status
                    case 'quest':
                    case 'item':                                    // markdown COULD implement enchantments/gems
                    case 'spell':
                        $spfVars[0] = $linkType;
                        break;
                    case 'talent':
                        if ($spell = DB::Aowow()->selectCell('SELECT `spell` FROM ::talents WHERE `id` = %i AND `rank` = %i', $linkVars[0], $linkVars[1]))
                        {
                            $spfVars[0] = 'spell';
                            $spfVars[1] = $spell;
                            break;
                        }
                    default:
                        return '';
                }

                switch ($fmt)
                {
                    case Lang::FMT_HTML:
                        return sprintf('<a href="?%s=%d">%s</a>', $spfVars);
                    case Lang::FMT_MARKUP:
                        return sprintf('[%s=%d]', $spfVars);
                    case Lang::FMT_RAW:
                    default:
                        return sprintf('(%s #%d) %s', $spfVars);
                }
            }, $text);

        // |1 - digit singular/plural    <number>|1<singular;<plural>; - Will choose a word depending on whether the digit preceding it is 0/1 or not (i.e. 1,11,21 return the first string, as will 0,10,40). Note that unlike |4 singular and plural forms are separated by semi-colon.
        $text = preg_replace_callback('/(\d+)\s*\|1([^;]+);([^;]+);/is', function ($m)
            {
                [$_, $num, $singular, $plural] = $m;

                switch ($num[-1])
                {
                    case 0:
                    case 1:
                        return $num . ' ' . $singular;
                    default:
                        return $num . ' ' . $plural;
                }
            }, $text);

        // |2 - frFR preposition: de     |2 <word> - Before vowels outputs d' (with apostrophe) and removes any leading spaces from text, otherwise outputs de (with trailing space)
        $text = preg_replace_callback('/\|2\s?(.)/i', function ($m)
            {
                [$_, $char] = $m;

                switch (strtolower($char))
                {
                    case 'h':
                        if (Lang::getLocale() != Locale::FR)
                            return 'de ' . $char;
                    case 'a':
                    case 'e':
                    case 'i':
                    case 'o':
                    case 'u':
                        return "d'" . $char;
                    default:
                        return 'de ' . $char;
                }
            }, $text);

        // |3 - ruRU declinations        |3-<caseIdx>(<word>) - Displays text declined to the specified form (index ranges from 1 to GetNumDeclensionSets()).
        $text = preg_replace_callback('/\|3-(\d+)\(([^\)]+)\)/iu', function ($m)
            {
                [$_, $caseIdx, $word] = $m;

                if ($caseIdx > 11 || $caseIdx < 1)          // max caseIdx seen in DeclinedWordCases.dbc
                    return $word;

                if (preg_match('/\P{Cyrillic}/iu', $word))  // not in cyrillic script
                    return $word;

                if ($declWord = DB::Aowow()->selectCell('SELECT dwc.`word` FROM ::declinedwordcases dwc JOIN ::declinedword dc ON dwc.`wordId` = dc.`id` WHERE dwc.`caseIdx` = %i AND dc.`word` = %s', $caseIdx, $word))
                    return $declWord;

                return $word;
            }, $text);

        // |4 - numeric switch           <number>           |4<singular>:<plural>[:<plural2>]; - Will choose a form based on the number preceding it. More than two forms (separated by colons) may be required by locale 8 (ruRU).
        $text = preg_replace_callback('/([\d\.\,]+)([^\d]*)\|4([^:]*):([^:;]+)(?::([^;]+))?;/is', function ($m)
            {
                [$_, $num, $pad, $singular, $plural1, $plural2] = array_pad($m, 6, null);

                if (Lang::getLocale() != Locale::RU || !$plural2)
                    return $num . $pad . ($num == 1 ? $singular : $plural1);

                // singular - ends in 1, but not teen number
                if ($num[-1] == 1 && $num != 11)
                    return $num . $pad . $singular;

                // genitive singular - ends in 2, 3, 4, but not teen number
                if (($num[-1] == 2 && $num != 12) || ($num[-1] == 3 && $num != 13) || ($num[-1] == 4 && $num != 14))
                    return $num . $pad . $plural1;

                // genitive plural - everything else
                return $num . $pad . $plural2;
            }, $text);

        return $text;
    }
}

?>
