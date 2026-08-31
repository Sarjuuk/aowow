<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Markup implements \JsonSerializable
{
    private const string DB_TAG_PATTERN = '/(?<!\\\\)\[(%s)=(-?\d+)([^\]])*\]/i';

    private const int IDX_DBTYPE         = 0;
    private const int IDX_SELF_CLOSED    = 1;
    private const int IDX_CONTENT_POLICY = 2;

    private const int STRIP_NONE = 0;                       // replace tag with jsGlobal data or dummy data; keep content
    private const int STRIP_TAG  = 1;                       // strip tag but keep content
    private const int STRIP_ALL  = 2;                       // strip everything

    private const array TAGS = array(
        // db types
        'achievement'       => [Type::ACHIEVEMENT, true,  self::STRIP_NONE, null],
        'class'             => [Type::CHR_CLASS,   true,  self::STRIP_NONE, null],
        'currency'          => [Type::CURRENCY,    true,  self::STRIP_NONE, null],
        'emote'             => [Type::EMOTE,       true,  self::STRIP_NONE, null],
        'enchantment'       => [Type::ENCHANTMENT, true,  self::STRIP_NONE, null],
        'event'             => [Type::WORLDEVENT,  true,  self::STRIP_NONE, null],
        'faction'           => [Type::FACTION,     true,  self::STRIP_NONE, null],
        'icondb'            => [Type::ICON,        true,  self::STRIP_NONE, null],
        'item'              => [Type::ITEM,        true,  self::STRIP_NONE, null],
        'itemset'           => [Type::ITEMSET,     true,  self::STRIP_NONE, null],
        'npc'               => [Type::NPC,         true,  self::STRIP_NONE, null],
        'object'            => [Type::OBJECT,      true,  self::STRIP_NONE, null],
        'pet'               => [Type::PET,         true,  self::STRIP_NONE, null],
        'quest'             => [Type::QUEST,       true,  self::STRIP_NONE, null],
        'race'              => [Type::CHR_RACE,    true,  self::STRIP_NONE, null],
        'skill'             => [Type::SKILL,       true,  self::STRIP_NONE, null],
        'sound'             => [Type::SOUND,       true,  self::STRIP_NONE, null],
        'spell'             => [Type::SPELL,       true,  self::STRIP_NONE, null],
        'statistic'         => [Type::STATISTIC,   true,  self::STRIP_NONE, null],
        'title'             => [Type::TITLE,       true,  self::STRIP_NONE, null],
        'zone'              => [Type::ZONE,        true,  self::STRIP_NONE, null],
        // other self-closing tags
        'achievementpoints' => [null,              true,  [self::class, 'handleAchievementPoints']],
        'anchor'            => [null,              true,  self::STRIP_ALL                         ],
        'br'                => [null,              true,  [self::class, 'handleBreak']            ],
        'db'                => [null,              true,  self::STRIP_ALL                         ],
        'feedback'          => [null,              true,  [self::class, 'handleFeedback']         ],
        'forumrules'        => [null,              true,  self::STRIP_ALL                         ],
        'hr'                => [null,              true,  self::STRIP_ALL                         ],
        'img'               => [null,              true,  self::STRIP_ALL                         ],
        'n5'                => [null,              true,  [self::class, 'handleN5']               ],
        'markupdoc'         => [null,              true,  self::STRIP_ALL                         ],
        'menu'              => [null,              true,  self::STRIP_ALL                         ],
        'money'             => [true,              true,  [self::class, 'handleMoney']            ],
        'pad'               => [null,              true,  self::STRIP_ALL                         ],
        'sig'               => [null,              true,  self::STRIP_ALL                         ],
        'time'              => [null,              true,  [self::class, 'handleTime']             ],
        'video'             => [null,              true,  self::STRIP_ALL                         ],
        'youtube'           => [null,              true,  self::STRIP_ALL                         ],
        // tag has children
        'acronym'           => [null,              false, self::STRIP_TAG],
        'b'                 => [null,              false, self::STRIP_TAG],
        'center'            => [null,              false, self::STRIP_TAG],
        'changelog'         => [null,              false, self::STRIP_ALL],
        'code'              => [null,              false, self::STRIP_ALL],
        'color'             => [null,              false, self::STRIP_TAG],
        'condition'         => [null,              false, self::STRIP_ALL],
        'copy'              => [null,              false, self::STRIP_ALL],
        'del'               => [null,              false, self::STRIP_TAG],
        'div'               => [null,              false, self::STRIP_TAG],
        'h2'                => [null,              false, self::STRIP_TAG],
        'h3'                => [null,              false, self::STRIP_TAG],
        'html'              => [null,              false, self::STRIP_ALL],
        'i'                 => [null,              false, self::STRIP_TAG],
        'icon'              => [null,              false, self::STRIP_ALL],
        'iconlist'          => [null,              false, self::STRIP_ALL],
        'ins'               => [null,              false, self::STRIP_TAG],
        'li'                => [null,              false, self::STRIP_TAG],
        'lightbox'          => [null,              false, self::STRIP_ALL],
        'map'               => [null,              false, self::STRIP_ALL],
        'pin'               => [null,              false, self::STRIP_ALL],
        'minibox'           => [null,              false, self::STRIP_ALL],
        'model'             => [null,              false, self::STRIP_ALL],
        'modelviewer'       => [null,              false, self::STRIP_ALL],
        'ol'                => [null,              false, self::STRIP_TAG],
        'p'                 => [null,              false, self::STRIP_TAG],
        'pre'               => [null,              false, self::STRIP_TAG],
        'quote'             => [null,              false, self::STRIP_ALL],
        'reveal'            => [null,              false, self::STRIP_TAG],
        's'                 => [null,              false, self::STRIP_TAG],
        'screenshot'        => [null,              false, self::STRIP_ALL],
        'script'            => [null,              false, self::STRIP_ALL],
        'section'           => [null,              false, self::STRIP_ALL],
        'small'             => [null,              false, self::STRIP_TAG],
        'span'              => [null,              false, self::STRIP_TAG],
        'spoiler'           => [null,              false, self::STRIP_TAG],
        'style'             => [null,              false, self::STRIP_ALL],
        'sub'               => [null,              false, self::STRIP_TAG],
        'sup'               => [null,              false, self::STRIP_TAG],
        'tabs'              => [null,              false, self::STRIP_ALL],
        'tab'               => [null,              false, self::STRIP_ALL],
        'table'             => [null,              false, self::STRIP_ALL],
        'tr'                => [null,              false, self::STRIP_ALL],
        'td'                => [null,              false, self::STRIP_ALL],
        'toc'               => [null,              false, self::STRIP_ALL],
        'toggler'           => [null,              false, self::STRIP_ALL],
        'tooltip'           => [null,              false, self::STRIP_ALL],
        'u'                 => [null,              false, self::STRIP_TAG],
        'ul'                => [null,              false, self::STRIP_TAG],
        'url'               => [null,              false, self::STRIP_TAG],
        'visitedpage'       => [null,              false, self::STRIP_ALL],
        'wowheadresponse'   => [null,              false, self::STRIP_ALL]
    );

    // there are more, but only these two are needed for preparing jsGlobals
    // note: tryFromDomain() does not understand 'www' as substitute for 'en'
    private const array GLOBAL_ATTRIBUTES = array(
        'site'   => [Locale::class, 'tryFromDomain'],
        'domain' => [Locale::class, 'tryFromDomain']
    );

    // const val
    public const int    MARKUP_MODE_COMMENT    = 1;
    public const int    MARKUP_MODE_ARTICLE    = 2;
    public const int    MARKUP_MODE_QUICKFACTS = 3;
    public const int    MARKUP_MODE_SIGNATURE  = 4;
    public const int    MARKUP_MODE_REPLY      = 5;

    // js var
    public const string MODE_COMMENT    = '$Markup.MODE_COMMENT';
    public const string MODE_ARTICLE    = '$Markup.MODE_ARTICLE';
    public const string MODE_QUICKFACTS = '$Markup.MODE_QUICKFACTS';
    public const string MODE_SIGNATURE  = '$Markup.MODE_SIGNATURE';
    public const string MODE_REPLY      = '$Markup.MODE_REPLY';

    // const val
    public const int    MARKUP_CLASS_ADMIN   = 40;
    public const int    MARKUP_CLASS_STAFF   = 30;
    public const int    MARKUP_CLASS_PREMIUM = 20;
    public const int    MARKUP_CLASS_USER    = 10;
    public const int    MARKUP_CLASS_PENDING = 1;

    // js var
    public const string CLASS_ADMIN   = '$Markup.CLASS_ADMIN';
    public const string CLASS_STAFF   = '$Markup.CLASS_STAFF';
    public const string CLASS_PREMIUM = '$Markup.CLASS_PREMIUM';
    public const string CLASS_USER    = '$Markup.CLASS_USER';
    public const string CLASS_PENDING = '$Markup.CLASS_PENDING';

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
                trigger_error(__METHOD__.' - unrecognized option: ' . $k);
        }

        $this->__text = $text;

        if ($parent)
            $this->__parent = $parent;
    }

    public function getJSGlobals() : array
    {
        return $this->_parseTags();
    }

    public function fillJSGlobals(array $jsgData = []) : string
    {
        return self::stripTags($this->__text, $jsgData);
    }

    public function getParent() : string
    {
        return $this->__parent;
    }


    /***********************/
    /* Markup tag handling */
    /***********************/

    private function _parseTags() : array
    {
        return self::parseTags($this->__text);
    }

    public static function parseTags(string $text) : array
    {
        $jsgStubs = [];

        $pattern = sprintf(self::DB_TAG_PATTERN, implode('|', array_keys(array_filter(self::TAGS, fn($x) => $x[self::IDX_DBTYPE]))));

        if (preg_match_all($pattern, $text, $matches, PREG_SET_ORDER | PREG_UNMATCHED_AS_NULL))
        {
            // todo - respect forced locale (and other global attributes?)
            // [achievement=3579 domain=ru], [spell=40120 site=fr]

            foreach ($matches as [, $tag, $id, $attrString])
            {
                $fn    = self::TAGS[$tag][self::IDX_CONTENT_POLICY];
                $attr  = self::parseTagAttributes($attrString);
                $attr += ['unnamed' => $id];

                if (is_callable($fn))
                    $fn($attr, [], $jsgStubs);
                else
                    $jsgStubs[self::TAGS[$tag][self::IDX_DBTYPE]][$id] = $id;
            }
        }

        return $jsgStubs;
    }

    public static function stripTags(string $text, array $jsgData = []) : string
    {
        // replace self closing tags
        $pattern = sprintf(self::DB_TAG_PATTERN, implode('|', array_keys(array_filter(self::TAGS, fn($x) => $x[self::IDX_SELF_CLOSED]))));

        $text = preg_replace_callback($pattern, function ($match) use ($jsgData)
        {
            [, $tag, $id, $attrString] = $match;

            $stripOrFn  = self::TAGS[$tag][self::IDX_CONTENT_POLICY];
            $type       = self::TAGS[$tag][self::IDX_DBTYPE];
            $attributes = self::parseTagAttributes($attrString) + ['unnamed' => $id];

            if (is_callable($stripOrFn))
                return $stripOrFn($attributes, $jsgData);
            else if ($stripOrFn == self::STRIP_ALL)
                return '';
            else if ($type)                                 // there rest is displayed in some way
                return $jsgData[$type][1][$id]['name'] ?? $jsgData[$type][1][$id]['name_' . Lang::getLocale()->json()] ?? Lang::main('parensFmt', ['', Util::ucFirst(Lang::game(Type::getFileString($type))).' #'.$id]);

            return '';

        }, $text, flags: PREG_UNMATCHED_AS_NULL);

        $stripped = '';                                     // text fragment storage
        $tagStack = [];                                     // [tagName, inheritedStip]

        // strip other Tags
        $len = mb_strlen($text);
        $textStart = $idx = 0;
        $open = $close = $isClose = false;
        $goodTag = true;

        $getValue = function(string $str) : array
        {
            $quote = $space = $value = null;
            if ($str[0] == '"' || $str[0] == "'")
            {
                $quote = $str[0];
                $end = mb_strpos($str, $quote, 1);
                if (is_int($end))
                {
                    $value = mb_substr($str, 1, $end - 1);
                    $str = trim(mb_substr($str, $end + 1 - 1));
                    return ['value' => htmlentities($value), 'str' =>  $str];
                }
            }

            $space = mb_strpos($str, ' ');
            if (is_int($space))
            {
                $value = mb_substr($str, 0, $space - 0);
                $str = trim(mb_substr($str, $space + 1 - 0));
            }
            else
            {
                $value = $str;
                $str = '';
            }

            return ['value' => $value, 'str' => $str];
        };

        while ($idx < $len)
        {
            $open = mb_strpos($text, '[', $idx);
            if (is_int($open))
            {
                $idx = $open + 1;
                if ($open > 0 && mb_substr($text, $open - 1, 1) == '\\')
                    $open = false;
                else
                    $close = mb_strpos($text, ']', $idx);
            }
            else
                $idx = $len;

            $tagName = '';
            $attrs   = [];

            if (is_int($close))
            {
                $tagContents = mb_substr($text, $open + 1, $close - $open - 1);
                if ($tagContents[0] == '/')
                {
                    $isClose = true;
                    $tagName = mb_strtolower(trim(mb_substr($tagContents, 1)));
                }

                if (!$isClose)
                {
                    $space  = mb_strpos($tagContents, ' ');
                    $assign = mb_strpos($tagContents, '=');
                    if (($assign < $space || $space === false) && is_int($assign))
                    {
                        $tagName = mb_strtolower(mb_substr($tagContents, 0, $assign));
                        $tagContents = trim(mb_substr($tagContents, $assign + 1));
                        $ret = $getValue($tagContents);
                        $tagContents = $ret['str'];
                        if (!isset(self::TAGS[$tagName]))
                            $goodTag = false;
                        else
                            $attrs['unnamed'] = $ret['value'];
                    }
                    else if (is_int($space))
                    {
                        $tagName = mb_strtolower(mb_substr($tagContents, 0, $space));
                        $tagContents = trim(mb_substr($tagContents, $space + 1));
                        if (mb_strpos($tagContents, '=') === false) // legacy support, [quote name]
                        {
                            if (!isset(self::TAGS[$tagName]))
                                $goodTag = false;
                            else
                                $attrs['unnamed'] = $tagContents;
                            $tagContents = '';
                        }
                    }
                    else
                    {
                        $tagName = mb_strtolower($tagContents);
                        $tagContents = '';
                    }

                    if (!isset(self::TAGS[$tagName]))
                        $goodTag = false;
                    else if ($goodTag)
                    {
                        while ($tagContents != '')
                        {
                            $attr = '';
                            if (!preg_match('/^\s*[a-z0-9]+\s*=/', $tagContents))
                                $attr = 'unnamed';
                            else
                            {
                                $assign = mb_strpos($tagContents, '=');
                                if ($assign === false)
                                {
                                    $goodTag = false;
                                    break;
                                }

                                $attr = mb_strtolower(trim(mb_substr($tagContents, 0, $assign)));
                                $tagContents = trim(mb_substr($tagContents, $assign + 1));
                            }

                            $ret = $getValue($tagContents);

                            $tagContents  = $ret['str'];
                            $attrs[$attr] = $ret['value'];
                        }
                    }
                }
                else if (!isset(self::TAGS[$tagName]))
                    $goodTag = false;
            }
            else
                $goodTag = false;

            if ($goodTag)
            {
                [$currentTag, $strip] = end($tagStack) ?: ['', false];

                if (!$strip && $textStart != $open)
                    $stripped .= str_replace('\\[', '[', mb_substr($text, $textStart, $open - $textStart));

                [, $selfClosed, $stripOrFn] = self::TAGS[$tagName];

                if ($stripOrFn == self::STRIP_NONE)         // add self to stripped text
                {
                    $attrs = [$tagName => $attrs['unnamed'] ?? ''] + $attrs;
                    unset($attrs['unnamed']);

                    array_walk($attrs, fn(&$v, $k) => $v = $k . ($v ? '='.$v : ''));

                    if ($isClose)
                        $stripped .= '[/'.$tagName.']';
                    else
                        $stripped .= '['.implode(' ', $attrs).']';
                }
                else if (is_callable($stripOrFn))
                    $stripped .= $stripOrFn($attrs, $jsgData);

                if ($isClose && $currentTag == $tagName)
                    array_pop($tagStack);
                else if (!$isClose && !$selfClosed)
                    array_push($tagStack, [$tagName, $strip || $stripOrFn == self::STRIP_ALL || is_callable($stripOrFn)]);

                $textStart = $idx = $close + 1;
            }

            $goodTag = true;
            $isClose = false;
            $open = $close = false;
        }

        if ($textStart < $len)
            $stripped .= str_replace('\\[', '[', mb_substr($text, $textStart));

        return $stripped;
    }

    private static function parseTagAttributes(?string $attributes) : array
    {
        if (!$attributes)
            return [];

        $attr = [];
        if (preg_match_all('/\b(\w+)=?([^ ]+)?\b/i', $attributes, $m, PREG_PATTERN_ORDER | PREG_UNMATCHED_AS_NULL))
            $attr = array_combine($m[1], $m[2]);

        return $attr;
    }


    /***************/
    /* Tag Handler */
    /***************/

    private static function handleAchievementPoints(array $attr) : string
    {
        return ($attr['unnamed'] ?? 0) . ' ' . Lang::game('acvmtPoints');
    }

    private static function handleBreak(array $attr) : string
    {
        return PHP_EOL;
    }

    private static function handleFeedback(array $attr) : string
    {
        return Cfg::get('CONTACT_EMAIL');
    }

    private static function handleN5(array $attr) : string
    {
        return Lang::nf($attr['unnamed'] ?? 0);
    }

    private static function handleTime(array $attr) : string
    {
        $now   = time();
        $delay = 0;

        if ($attr['until'] ?? null)
            $delay = $attr['until'] - $now;
        else
            $delay = $now - ($attr['since'] ?? 0);

        if ($delay > 0)
            return DateTime::formatTimeElapsed($delay * 1000);

        return '0 ' . Lang::timeUnits('sg', 6);
    }

    private static function handleMoney(array $attr, array $jsgData, array &$jsgStubs = []) : string
    {
        $moneys = [];

        if ($gold = ($attr['unnamed'] ?? 0))
        {
            if ($g = intdiv($gold, 10000))
                $moneys[] = Lang::game('gold', [$g]);

            if ($s = intdiv($gold % 10000, 100))
                $moneys[] = Lang::game('silver', [$s]);

            if ($c = ($gold % 100))
                $moneys[] = Lang::game('copper', [$c]);
        }

        if (isset($attr['honor']))
            $moneys[] = $attr['honor'] . ' ' . Lang::game('honorPoints');
        if (isset($attr['arena']))
            $moneys[] = $attr['arena'] . ' ' . Lang::game('arenaPoints');

        if (isset($attr['items']))
        {
            $x = explode(',', $attr['items']);
            for ($i = 0; $i < count($x); $i+=2)
            {
                if (!isset($jsgData[Type::ITEM][1][$x[$i]]))
                {
                    $jsgStubs[Type::ITEM][$x[$i]] = $x[$i];
                    $moneys[] = Lang::main('parensFmt', [$x[$i + 1] ?? 0, Util::ucFirst(Lang::game('item')).' #'.$x[$i]]);
                }
                else
                    $moneys[] = ($x[$i + 1] ?? 0) . ' ' . $jsgData[Type::ITEM][1][$x[$i]]['name'] ?? $jsgData[Type::ITEM][1][$x[$i]]['name_' . Lang::getLocale()->json()];
            }
        }

        if (isset($attr['currency']))
        {
            $x = explode(',', $attr['currency']);
            for ($i = 0; $i < count($x); $i+=2)
            {
                if (!isset($jsgData[Type::CURRENCY][$x[$i]]))
                {
                    $jsgStubs[Type::CURRENCY][$x[$i]] = $x[$i];
                    $moneys[] = Lang::main('parensFmt', [$x[$i + 1] ?? 0, Util::ucFirst(Lang::game('curency')).' #'.$x[$i]]);
                }
                else
                    $moneys[] = ($x[$i + 1] ?? 0) . ' ' . $jsgData[Type::CURRENCY][1][$x[$i]]['name'] ?? $jsgData[Type::CURRENCY][1][$x[$i]]['name_' . Lang::getLocale()->json()];
            }
        }

        return Lang::concat($moneys);
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

    /** break html tags, unify newlines */
    private function cleanText() : string
    {
        $txt = strtr($this->__text, array(
            "\r\n" => "\n",
            "\r"   => "\n"
        ));

        try
        {
            return json_encode($txt, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_THROW_ON_ERROR);
        }
        catch (\JsonException $e)
        {
            trigger_error(__METHOD__.' - '.$e->getMessage(), E_USER_WARNING);
            return '';
        }
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
        if ($attr = $this->jsonSerialize())
            return 'Markup.printHtml('.$this->cleanText().', "'.$this->__parent.'", '.Util::toJSON($attr).');'.PHP_EOL;

        return 'Markup.printHtml('.$this->cleanText().', "'.$this->__parent.'");'.PHP_EOL;
    }
}

?>
