<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Markup implements \JsonSerializable
{
    private const DB_TAG_PATTERN = '/(?<!\\\\)\[(npc|object|item|itemset|quest|spell|zone|faction|pet|achievement|statistic|title|event|class|race|skill|currency|emote|enchantment|money|sound|icondb)=(-?\d+)[^\]]*\]/i';

    // const val
    public const MARKUP_MODE_COMMENT    = 1;
    public const MARKUP_MODE_ARTICLE    = 2;
    public const MARKUP_MODE_QUICKFACTS = 3;
    public const MARKUP_MODE_SIGNATURE  = 4;
    public const MARKUP_MODE_REPLY      = 5;

    // js var
    public const MODE_COMMENT    = '$Markup.MODE_COMMENT';
    public const MODE_ARTICLE    = '$Markup.MODE_ARTICLE';
    public const MODE_QUICKFACTS = '$Markup.MODE_QUICKFACTS';
    public const MODE_SIGNATURE  = '$Markup.MODE_SIGNATURE';
    public const MODE_REPLY      = '$Markup.MODE_REPLY';

    // const val
    public const MARKUP_CLASS_ADMIN   = 40;
    public const MARKUP_CLASS_STAFF   = 30;
    public const MARKUP_CLASS_PREMIUM = 20;
    public const MARKUP_CLASS_USER    = 10;
    public const MARKUP_CLASS_PENDING = 1;

    // js var
    public const CLASS_ADMIN   = '$Markup.CLASS_ADMIN';
    public const CLASS_STAFF   = '$Markup.CLASS_STAFF';
    public const CLASS_PREMIUM = '$Markup.CLASS_PREMIUM';
    public const CLASS_USER    = '$Markup.CLASS_USER';
    public const CLASS_PENDING = '$Markup.CLASS_PENDING';

    // options
    private ?string $prepend     = null;                    // html in front of article
    private ?string $append      = null;                    // html trailing the article
    private ?int    $locale      = null;                    // forces tooltips in the article to adhere to another locale
    private ?int    $inBlog      = null;                    // js:bool; unused by aowow
    private ?string $mode        = null;                    // defaults to Markup.MODE_ARTICLE, which is what we want.
    private ?string $allow       = null;                    // defaults to Markup.CLASS_STAFF
    private ?int    $roles       = null;                    // if allow is null, get allow from roles (user group); also mode will be set to MODE_ARTICLE for staff groups
    private ?int    $stopAtBreak = null;                    // js:bool; only parses text until substring "[break]" is encountered; some debug option...?
    private ?string $highlight   = null;                    // HTMLNode selector

    private ?int    $skipReset   = null;                    // js:bool; unsure, if TRUE the next settings in this block get skipped
    private ?string $uid         = null;                    // defaults to 'abc'; unsure, key under which media is stored and referenced in g_screenshots and g_videos
    private ?string $root        = null;                    // unsure, something to with Markup Tags that need to be subordinate to other tags (e.g.: [li] to [ol])
    private ?int    $preview     = null;                    // unsure, appends '-preview' to the div created by the [tabs] tag and prevents scrolling. Forum feature?
    private ?int    $dbpage      = null;                    // js:bool; set on db type detail pages; adds article edit links to admin menu

    protected string $__text;

    private string $__parent = 'article-generic';

    public function __construct(string $text, array $opts, string $parent = '')
    {
        foreach ($opts as $k => $v)
        {
            if (property_exists($this, $k))
                $this->$k = $v;
            else
                trigger_error(self::class.'::__construct - unrecognized option: ' . $k);
        }

        $this->__text = $text;

        if ($parent)
            $this->__parent = $parent;
    }

    public function getJsGlobals() : array
    {
        return $this->_parseTags();
    }

    public function getParent() : string
    {
        return $this->__parent;
    }


    /***********************/
    /* Markup tag handling */
    /***********************/

    private function _parseTags(array &$jsg = []) : array
    {
        return self::parseTags($this->__text, $jsg);
    }

    public static function parseTags(string $text, array &$jsg = []) : array
    {
        $jsGlobals = [];

        if (preg_match_all(self::DB_TAG_PATTERN, $text, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                if ($match[1] == 'statistic')
                    $match[1] = 'achievement';
                else if ($match[1] == 'icondb')
                    $match[1] = 'icon';

                // todo - respecte forced locale
                // match[0] => [achievement=3579 domain=ru], [spell=40120 site=fr]

                if ($match[1] == 'money')
                {
                    if (stripos($match[0], 'items'))
                    {
                        if (preg_match('/items=([0-9,]+)/i', $match[0], $submatch))
                        {
                            $sm = explode(',', $submatch[1]);
                            for ($i = 0; $i < count($sm); $i+=2)
                                $jsGlobals[Type::ITEM][$sm[$i]] = $sm[$i];
                        }
                    }

                    if (stripos($match[0], 'currency'))
                    {
                        if (preg_match('/currency=([0-9,]+)/i', $match[0], $submatch))
                        {
                            $sm = explode(',', $submatch[1]);
                            for ($i = 0; $i < count($sm); $i+=2)
                                $jsGlobals[Type::CURRENCY][$sm[$i]] = $sm[$i];
                        }
                    }
                }
                else if ($type = Type::getIndexFrom(Type::IDX_FILE_STR, $match[1]))
                    $jsGlobals[$type][$match[2]] = $match[2];
            }
        }

        Util::mergeJsGlobals($jsg, $jsGlobals);

        return $jsGlobals;
    }

    private function _stripTags(array $jsgData = []) : string
    {
        return self::stripTags($this->__text, $jsgData);
    }

    public static function stripTags(string $text, array $jsgData = []) : string
    {
        // replace DB Tags
        $text = preg_replace_callback(self::DB_TAG_PATTERN, function ($match) use ($jsgData) {
            if ($match[1] == 'statistic')
                $match[1] = 'achievement';
            else if ($match[1] == 'icondb')
                $match[1] = 'icon';
            else if ($match[1] == 'money')
            {
                $moneys = [];
                if (stripos($match[0], 'items'))
                {
                    if (preg_match('/items=([0-9,]+)/i', $match[0], $submatch))
                    {
                        $sm = explode(',', $submatch[1]);
                        for ($i = 0; $i < count($sm); $i += 2)
                        {
                            if (!empty($jsgData[Type::ITEM][1][$sm[$i]]))
                                $moneys[] = $jsgData[Type::ITEM][1][$sm[$i]]['name'] ?? $jsgData[Type::ITEM][1][$match[2]]['name_' . Lang::getLocale()->json()];
                            else
                                $moneys[] = Util::ucFirst(Lang::game('item')).' #'.$sm[$i];
                        }
                    }
                }

                if (stripos($match[0], 'currency'))
                {
                    if (preg_match('/currency=([0-9,]+)/i', $match[0], $submatch))
                    {
                        $sm = explode(',', $submatch[1]);
                        for ($i = 0; $i < count($sm); $i += 2)
                        {
                            if (!empty($jsgData[Type::CURRENCY][1][$sm[$i]]))
                                $moneys[] = $jsgData[Type::CURRENCY][1][$sm[$i]]['name'] ?? $jsgData[Type::CURRENCY][1][$match[2]]['name_' . Lang::getLocale()->json()];
                            else
                                $moneys[] = Util::ucFirst(Lang::game('curency')).' #'.$sm[$i];
                        }
                    }
                }

                return Lang::concat($moneys);
            }
            if ($type = Type::getIndexFrom(Type::IDX_FILE_STR, $match[1]))
            {
                if (!empty($jsgData[$type][1][$match[2]]))
                    return $jsgData[$type][1][$match[2]]['name'] ?? $jsgData[$type][1][$match[2]]['name_' . Lang::getLocale()->json()];
                else
                    return Util::ucFirst(Lang::game($match[1])).' #'.$match[2];
            }

            trigger_error('Markup::stripTags() - encountered unhandled db-tag: '.var_export($match));
            return '';
        }, $text);

        // replace line endings
        $text = str_replace('[br]', "\n",  $text);

        // strip other Tags
        $stripped = '';
        $inTag = false;
        for ($i = 0; $i < strlen($text); $i++)
        {
            if ($text[$i] == '[' && (!$i || $text[$i - 1] != '\\'))
                $inTag = true;
            if (!$inTag)
                $stripped .= $text[$i];
            if ($inTag && $text[$i] == ']' && (!$i || $text[$i - 1] != '\\'))
                $inTag = false;
        }

        return $stripped;
    }


    /*********************/
    /* String Operations */
    /*********************/

    public function append(string $text) : self
    {
        $this->__text .= $text;
        return $this;
    }

    public function prepend(string $text) : self
    {
        $this->__text = $text . $this->__text;
        return $this;
    }

    public function apply(\Closure $fn) : void
    {
        $this->__text = $fn($this->__text);
    }

    public function replace(string $middle, int $offset = 0, ?int $len = null) : self
    {
        // y no mb_substr_replace >:(
        $start = $end = '';

        if ($offset < 0)
            $offset = mb_strlen($this->__text) + $offset;

        $start = mb_substr($this->__text, 0, $offset);

        if (!is_null($len) && $len >= 0)
            $end = mb_substr($this->__text, $offset + $len);
        else if (!is_null($len) && $len < 0)
            $end = mb_substr($this->__text, $offset + mb_strlen($this->__text) + $len);

        $this->__text = $start . $middle . $end;
        return $this;
    }

    private function cleanText() : string
    {
        // break script-tags, unify newlines
        $val = preg_replace(['/script\s*\>/i', "/\r\n/", "/\r/"], ['script>', "\n", "\n"], $this->__text);

        return strtr(Util::jsEscape($val), ['script>' => 'scr"+"ipt>']);
    }

    public function jsonSerialize() : array
    {
        $result = [];

        foreach ($this as $prop => $val)
            if ($val !== null && $prop[0] != '_')
                $result[$prop] = $val;

        return $result;
    }

    public function __toString() : string
    {
        if ($this->jsonSerialize())
            return 'Markup.printHtml("'.$this->cleanText().'", "'.$this->__parent.'", '.Util::toJSON($this).");\n";

        return 'Markup.printHtml("'.$this->cleanText().'", "'.$this->__parent."\");\n";
    }
}

?>
