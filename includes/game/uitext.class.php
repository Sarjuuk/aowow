<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


/**
 * collection of functions to handle various FrameXML excentricities
 */
final class UIText
{
    private const array LETTER_HAS_FINAL_CONSONANT = [0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0];
    private const array DIGIT_HAS_FINAL_CONSONANT  = [1, 1, 0, 1, 0, 0, 1, 1, 1, 0];

    private const array LINE_BREAK = array(
        -1               => ' ',
        Lang::FMT_RAW    => "\n",
        Lang::FMT_HTML   => '<br />',
        Lang::FMT_MARKUP => '[br]'
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

        if (stripos($text, '<HTML>') !== false)
            $text = self::handleSimpleHtml($text, $fmt);
        else
        {
            // if target output is html escape fake html-ish tags the browser skipsh dishplaying ...<hic>!
            if ($fmt == Lang::FMT_HTML)
                $text = Util::htmlEscape($text);

            $text = strtr($text, ["\n" => self::LINE_BREAK[$fmt]]);
        }

        if (strpos($text, '|') !== false)
            $text = self::unescapeUISequences($text, $fmt);

        if (strpos($text, '$') !== false)
            $text = self::replaceTextVariables($text, $fmt);

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
            ['<HTML>', '</HTML>', '<BODY>', '</BODY>', '<BR></BR>',           ],
            ['',       '',        '',       '',         self::LINE_BREAK[$fmt]],
            $text
        );

        // redirect 'src' of <IMG> tags
        $text = preg_replace_callback('/src="([^"]+)"/i', fn($m) => sprintf('src="%s/images/wow/%s.png"', Cfg::get('STATIC_URL'), strtr($m[1], ['\\' => '/'])), $text);

        // docs say SimpleHTML supports anchors though in 335 they seem to be unused
        // also, where the hell do they link to? For now strip the anchor tags and retain the contained text node.
        $text = preg_replace('/<a href="[^"]*">([^<]*)<\/a>/ui', '\1', $text);

        // make tags fit the output format                          v IMG+BR tags have this trailing self-closing slash
        $text = preg_replace_callback('/<(\/?)([a-z1-3]+) ?([^>]*?)(\/?)>/i', function ($m) use ($fmt) {
            if ($fmt == Lang::FMT_RAW)
                return '';

            if ($fmt == Lang::FMT_MARKUP && self::validateTag(...$m))
                return '['.substr($m[0], 1, $m[4] ? -2 : -1).']'; // markup does not use trailing self-closing slashes

            if ($fmt == Lang::FMT_HTML && !self::validateTag(...$m))
                return '&lt;'.substr($m[0], 1, -1).'&gt;';

            return $m[0];
        }, $text);

        return $text;
    }

    /**
     * Validates a parsed UI tag against the supported tag set and required attributes.
     *
     * @param string $full     Full tag text. (not used, required to spread regex match into function)
     * @param string $closing  Whether the tag is a closing tag.
     * @param string $tag      Tag name to validate.
     * @param string $attrStr  Raw attribute string for the tag.
     *
     * @return bool True when the tag is allowed and valid, otherwise false.
     */
    private static function validateTag(string $full, string $closing, string $tag, string $attrStr) : bool
    {
        if ($closing && trim($attrStr))
            return false;

        $tag = strtolower($tag);

        switch ($tag)
        {
            case 'h1':
            case 'h2':
            case 'h3':
            case 'span':
            case 'br':
            case 'p':
                return true;
        }

        /*
         * i.e. quest 13081 reward text:
         * <A gentle ringing fills your head as you approach the naaru.> [...]
         * looks like an anchor but SimpleXMLElement throws an exception as intended
         */

        try
        {
            $node = new SimpleXML($full);
        }
        catch (\Exception $e)
        {
            return false;
        }

        return match ($tag)
        {
            'a'     => $closing || !empty($node->attributes()['href']),
            'img'   => !empty($node->attributes()['src']),
            default => false
        };
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
            '[span class=q0]WorldState #\1[/span]',
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

        // line break                           |n
        $text = preg_replace_callback('/(?<!\|)\|n/i', function ($m) use ($fmt)
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

        // color                                |c<aarrggbb><word>|r
        $text = preg_replace_callback('/(?<!\|)\|c([[:xdigit:]]{2})([[:xdigit:]]{6})(.+?)\|r/is', function ($m) use ($fmt)
            {
                [$_, $a, $rgb, $text] = $m;

                switch ($fmt)
                {
                    case -1:                                // default Lang::vspf case
                    case Lang::FMT_HTML:
                        return sprintf('<span style="color: #%1$s%2$s;">%3$s</span>', $rgb, $a, $text);
                    case Lang::FMT_MARKUP:
                        return sprintf('[color=#%1$s]%3$s[/color]', $rgb, $a, $text); // doesn't support alpha
                    case Lang::FMT_RAW:
                    default:
                        return $text;
                }
            }, $text);

        // icon                                 |T<imgPath+File.blp>:0:0:0:-1|t
        $text = preg_replace_callback('/(?<!\|)\|T([\w]+\\\)*([^\.:]+)(?:\.blp)?:([^\|]+)\|t/i', function ($m) use ($fmt)
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
                        return '[icon name='.Util::lower($iconName).'][/icon]';
                    case Lang::FMT_RAW:
                    default:
                        return '';
                }
            }, $text);

        // hyperlink                            |H<hyperlinkStruct>|h<name>|h
        $text = preg_replace_callback('/(?<!\|)\|H([^:]+):([^\|]+)\|h([^\|]+)\|h/i', function ($m) use ($fmt)
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
                        return vsprintf('<a href="?%s=%d">%s</a>', $spfVars);
                    case Lang::FMT_MARKUP:
                        return vsprintf('[%s=%d]', $spfVars);
                    case Lang::FMT_RAW:
                    default:
                        return vsprintf('(%s #%d) %s', $spfVars);
                }
            }, $text);

        // |1 - particle selector: <lastChar> <padding> |1 <consonant> ; <vowel> ;
        // Will choose a word depending on the last character
        $text = preg_replace_callback('/(\p{L})(\P{L}*(?: *<[^>]+> *)*)(?<!\|)\|1(\D[^;]+);([^;]+);/uis', function ($m)
            {
                [, $char, $pad, $cf, $vf] = $m;

                return $char . $pad . self::languageRule1ChooseParticle(mb_ord($char), $cf, $vf);
            }, $text);

        // |2 - frFR preposition (de): |2 <padding> <word>
        $text = preg_replace_callback('/(?<!\|)\|2((?: *<[^>]+> *)*)([^ ]{4})/i', function ($m)
            {
                [, $pad, $word] = $m;

                $pairs = array(
                    "L'"   => "de l'",  "Le "  => "du ",
                    "La "  => "de la ", "Les " => "des ",
                );

                foreach ($pairs as $find => $replace)
                    if (str_starts_with($word, $find))
                        return $pad . substr_replace($word, $replace, 0, strlen($find));

                if (self::languageRule2IsVowel($word[0]) || $word[0] == 'h' || $word[0] == 'H' ||
                    ($word[0] == 'y' && self::languageRule2IsVowel($word[1])) ||
                    ($word[0] == 'Y' && self::languageRule2IsVowel($word[1])))
                    return "d'" . $pad . $word;

                return "de " . $pad . $word;
            }, $text);

        // |3 - ruRU declinations: |3- <caseIdx> ( <padding> <word> <padding> )
        // Displays text declined to the specified form (index ranges from 1 to GetNumDeclensionSets()).
        $text = preg_replace_callback('/(?<!\|)\|3-(\d+)\((.*?)([ \p{Cyrillic}]+)(.*?)\)/iu', function ($m)
            {
                [, $caseIdx, $padPre, $word, $padPost] = $m;

                if ($caseIdx > 11 || $caseIdx < 1)          // max caseIdx seen in DeclinedWordCases.dbc
                    return $padPre . $word . $padPost;

                if ($declWord = DB::Aowow()->selectCell('SELECT dwc.`word` FROM ::declinedwordcases dwc JOIN ::declinedword dc ON dwc.`wordId` = dc.`id` WHERE dwc.`caseIdx` = %i AND dc.`word` = %s', $caseIdx, $word))
                    return $padPre . $declWord . $padPost;

                return $padPre . $word . $padPost;
            }, $text);

        // |4 - numeric switch: <number> <padding> |4 <singular> : <plural>[ : <plural2>] ;
        // Will choose a form based on the number preceding it. More than two forms (separated by colons) may be required by locale 8 (ruRU).
        $text = preg_replace_callback('/\b([\d\.\,]*?\d+)\b([^\|]*)(?<!\|)\|4([^:]*):([^:;]+)(?::([^;]+))?;/is', function ($m)
            {
                [, $num, $pad, $singular, $plural1, $plural2] = array_pad($m, 6, null);

                return $num . $pad . match(self::getPluralIndex($num))
                {
                    0 => $singular,
                    1 => $plural1,                          // ruRU: genitive singular
                    2 => $plural2                           // ruRU: genitive plural
                };
            }, $text);

        // unescape escaped ui sequences
        return strtr($text, ['||' => '|']);
    }


    private static function getPluralIndex(int $num) : int
    {
        $pluralRule = match (Lang::getLocale())
        {
            Locale::KR, Locale::FR,
            Locale::CN, Locale::TW => 1,
            Locale::RU             => 2,
            default                => 0
        };

        if ($pluralRule == 0)
            return $num != 1;

        if ($pluralRule == 1)
            return $num > 1;

        if ($num % 100 >= 11 && $num % 100 <= 14)
            return 2;

        if ($num % 10 == 1)
            return 0;

        if ($num % 10 >= 2 && $num % 10 <= 4)
            return 1;

        return 2;
    }

    private static function languageRule1ChooseParticle(int $lastChar, string $consonantForm, string $vowelForm) : string
    {
        if ($lastChar & 0xFF00)
        {
            // CN/TW characters
            if ((($lastChar + 0x5400) & 0xFFFF) > 0x2BA3)
                return $consonantForm;

            $syllable = $lastChar & 0xFFFF;

            // KR
            if (($syllable - 0xAC08) % 28 == 0 && $consonantForm == '으')
                return $vowelForm;

            return ($syllable - 0xAC00) % 28 ? $consonantForm : $vowelForm;
        }

        // ASCII number offset
        if ($lastChar - 48 <= 9)
            return self::DIGIT_HAS_FINAL_CONSONANT[$lastChar - 48] ? $consonantForm : $vowelForm;

        // ASCII letter offsets lc
        if ($lastChar >= 97 && $lastChar <= 122)
            return self::LETTER_HAS_FINAL_CONSONANT[$lastChar - 97] ? $consonantForm : $vowelForm;

        // ASCII letter offsets uc
        if ($lastChar >= 65 && $lastChar <= 90)
            return self::LETTER_HAS_FINAL_CONSONANT[$lastChar - 65] ? $consonantForm : $vowelForm;

        return $consonantForm;
    }

    private static function languageRule2IsVowel(string $char) : bool
    {
        switch ($char)
        {
            case 'A':
            case 'E':
            case 'I':
            case 'O':
            case 'U':
            case 'a':
            case 'e':
            case 'i':
            case 'o':
            case 'u':
                return true;
        }

        $ch = mb_ord($char);
        return ($ch >= 0xC0 && $ch <= 0xC6) ||              // À Á Â Ã Ä Å Æ
               ($ch >= 0xC8 && $ch <= 0xCF) ||              // È É Ê Ë Ì Í Î Ï
               ($ch >= 0xD2 && $ch <= 0xD6) ||              // Ò Ó Ô Õ Ö
               ($ch >= 0xD8 && $ch <= 0xDC) ||              // Ø Ù Ú Û Ü
               ($ch >= 0xE0 && $ch <= 0xE6) ||              // à á â ã ä å æ
               ($ch >= 0xE8 && $ch <= 0xEF) ||              // è é ê ë ì í î ï
               ($ch >= 0xF2 && $ch <= 0xF6) ||              // ò ó ô õ ö
               ((($ch - 0xF8) & 0xFFFF)) <= 4;              // ø ù ú û ü
    }

}

?>
