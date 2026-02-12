<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');



// PHP 8.4 polyfill
if (version_compare(PHP_VERSION, '8.4.0') < 0)
{
    function array_find(array $array, callable $callback) : mixed
    {
        foreach ($array as $k => $v)
            if ($callback($v, $k))
                return $array[$k];
        return null;
    }

    function array_find_key(array $array, callable $callback) : mixed
    {
        foreach ($array as $k => $v)
            if ($callback($v, $k))
                return $k;
        return null;
    }
}

class SimpleXML extends \SimpleXMLElement
{
    public function addCData(string $cData) : \SimpleXMLElement
    {
        $node = dom_import_simplexml($this);
        $no   = $node->ownerDocument;
        $node->appendChild($no->createCDATASection($cData));

        return $this;
    }
}


abstract class Util
{
    /* NOTE!
     * FILE_ACCESS should be 0755 or less, but CLI and web interface both access the same files. While in CLI php is executed with the current users perms,
     * while the web interface is always executed by www-data (or whoever runs the web server) who does not own the files previously created via CLI.
     * And thus web interface actions fail with permission denied, unless the files are flagged +wx for everyone.
     * This probably has to be solved on the system level by having www-data and the CLI user share a group or something.
     */
    public const FILE_ACCESS = 0777;
    public const DIR_ACCESS  = 0777;

    private const GEM_SCORE_BASE_WOTLK = 16;                // rare quality wotlk gem score
    private const GEM_SCORE_BASE_BC    = 8;                 // rare quality bc gem score

    private static $perfectGems             = null;

    public static $regions                  = array(
        'us',           'eu',           'kr',           'tw',           'cn',           'dev'
    );

    public static $ssdMaskFields            = array(
        'shoulderMultiplier',           'trinketMultiplier',            'weaponMultiplier',             'primBudged',
        'rangedMultiplier',             'clothShoulderArmor',           'leatherShoulderArmor',         'mailShoulderArmor',
        'plateShoulderArmor',           'weaponDPS1H',                  'weaponDPS2H',                  'casterDPS1H',
        'casterDPS2H',                  'rangedDPS',                    'wandDPS',                      'spellPower',
        null,                           null,                           'tertBudged',                   'clothCloakArmor',
        'clothChestArmor',              'leatherChestArmor',            'mailChestArmor',               'plateChestArmor'
    );

    public static $dateFormatInternal       = "Y/m/d H:i:s";

    public static $changeLevelString        = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_staticTooltipLevelClick(this, null, 0)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"><!--lvl-->%s</a>';
    public static $setRatingLevelString     = '<a href="javascript:;" onmousedown="return false" class="tip" style="color: white; cursor: pointer" onclick="$WH.g_setRatingLevel(this, %s, %s, %s)" onmouseover="$WH.Tooltip.showAtCursor(event, \'<span class=\\\'q2\\\'>\' + LANG.tooltip_changelevel + \'</span>\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">%s</a>';
    public static $lvTabNoteString          = '<b class="tip" onmouseover="$WH.Tooltip.showAtCursor(event, \'%s\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()">%s</b>';

    public static $filterResultString       = '$$WH.sprintf(LANG.lvnote_filterresults, \'%s\')';
    public static $tryFilteringString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryFilteringEntityString = '$$WH.sprintf(LANG.lvnote_entitiesfound, %s, %s, %s) + LANG.dash + LANG.lvnote_tryfiltering.replace(\'<a>\', \'<a href="javascript:;" onclick="fi_toggle()">\')';
    public static $tryNarrowingString       = '$$WH.sprintf(%s, %s, %s) + LANG.dash + LANG.lvnote_trynarrowing';

    public static $dfnString                = '<dfn title="%s" class="w">%s</dfn>';

    public static $mapSelectorString        = '<a href="javascript:;" onclick="myMapper.update({zone: %d}); g_setSelectedLink(this, \'mapper\'); return false" onmousedown="return false">%s</a>&nbsp;(%d)';

    public static $expansionString          = [null, 'bc', 'wotlk'];

    public static $tcEncoding               = '0zMcmVokRsaqbdrfwihuGINALpTjnyxtgevElBCDFHJKOPQSUWXYZ123456789';
    private static $notes                   = [];

    public static function addNote(string $note, int $uGroupMask = U_GROUP_EMPLOYEE, int $level = LOG_LEVEL_ERROR) : void
    {
        self::$notes[] = [$note, $uGroupMask, $level];
    }

    public static function getNotes() : array
    {
        $notes = [];
        $severity = LOG_LEVEL_INFO;
        foreach (self::$notes as $k => [$note, $uGroup, $level])
        {
            if ($uGroup && !User::isInGroup($uGroup))
                continue;

            if ($level < $severity)
                $severity = $level;

            $notes[] = $note;
            unset(self::$notes[$k]);
        }

        return [$notes, $severity];
    }

    public static function formatMoney(int $qty) : string
    {
        $money = '';

        if ($qty >= 10000)
        {
            $g = floor($qty / 10000);
            $money .= '<span class="moneygold">'.$g.'</span> ';
            $qty -= $g * 10000;
        }

        if ($qty >= 100)
        {
            $s = floor($qty / 100);
            $money .= '<span class="moneysilver">'.$s.'</span> ';
            $qty -= $s * 100;
        }

        if ($qty > 0)
            $money .= '<span class="moneycopper">'.$qty.'</span>';

        return $money;
    }

    // pageTexts, questTexts and mails
    public static function parseHtmlText(string|array $text, bool $markdown = false) : string|array
    {
        if (is_array($text))
        {
            foreach ($text as &$t)
                $t = self::parseHtmlText($t, $markdown);

            return $text;
        }

        if (stristr($text, '<HTML>'))                       // text is basically a html-document with weird linebreak-syntax
        {
            $pairs = array(
                '<HTML>'    => '',
                '</HTML>'   => '',
                '<BODY>'    => '',
                '</BODY>'   => '',
                '<BR></BR>' => $markdown ? '[br]' : '<br />'
            );

            // html may contain 'Pictures' and FlavorImages and "stuff"
            $text = preg_replace_callback(
                '/src="([^"]+)"/i',
                function ($m) { return sprintf('src="%s/images/wow/%s.png"', Cfg::get('STATIC_URL'), strtr($m[1], ['\\' => '/'])); },
                strtr($text, $pairs)
            );
        }
        else
            $text = strtr($text, ["\n" => $markdown ? '[br]' : '<br />', "\r" => '']);

        // escape fake html-ish tags the browser skipsh dishplaying ...<hic>!
        $text = preg_replace('/<([^\s\/]+)>/iu', '&lt;\1&gt;', $text);

        $from = array(
            '/\$g\s*([^:;]*)\s*:\s*([^:;]*)\s*(:?[^:;]*);/ui',// directed gender-reference                      $g<male>:<female>:<refVariable>
            '/\$t([^;]+);/ui',                              // HK rank. $t<male>:<female>; (maybe male/female if pvp unranked? Gets replaced with current HK rank.)
            '/<([^\"=\/>]+\s[^\"=\/>]+)>/ui',               // emotes (workaround: at least one whitespace and never " or = between brackets)
            '/\$(\d+)w/ui',                                 // worldState(?)-ref found on some pageTexts        $1234w
            '/\$c/i',                                       // class-ref
            '/\$r/i',                                       // race-ref
            '/\$n/i',                                       // name-ref
            '/\$b/i'                                        // line break
        );

        $toMD = array(
            '<\1/\2>',
            '<'.implode('/', Lang::game('pvpRank', 1)).'>',
            '<\1>',
            '[span class=q0>WorldState #\1[/span]',
            '<'.Lang::game('class').'>',
            '<'.Lang::game('race').'>',
            '<'.Lang::main('name').'>',
            '[br]'
        );

        $toHTML = array(
            '&lt;\1/\2&gt;',
            '&lt;'.implode('/', Lang::game('pvpRank', 1)).'&gt;',
            '&lt;\1&gt;',
            '<span class="q0">WorldState #\1</span>',
            '&lt;'.Lang::game('class').'&gt;',
            '&lt;'.Lang::game('race').'&gt;',
            '&lt;'.Lang::main('name').'&gt;',
            '<br />'
        );

        $text = preg_replace($from, $markdown ? $toMD : $toHTML, $text);

        return Lang::unescapeUISequences($text, $markdown ? Lang::FMT_MARKUP : Lang::FMT_HTML);
    }

    public static function asHex(int $val) : string
    {
        $_ = decHex($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return '0x'.strToUpper($_);
    }

    public static function asBin(int $val) : string
    {
        $_ = decBin($val);
        while (fMod(strLen($_), 4))                         // in 4-blocks
            $_ = '0'.$_;

        return 'b'.$_;
    }

    public static function htmlEscape(string|array|null $data) : string|array
    {
        if (empty($data))                                   // null, '', [] and not "0"
            return '';

        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::htmlEscape($v);

            return $data;
        }

        return htmlspecialchars($data, ENT_QUOTES | ENT_DISALLOWED | ENT_HTML5, 'utf-8');
    }

    public static function jsEscape(string|array|null $data) : string|array
    {
        if (empty($data))                                   // null, '', [] and not "0"
            return '';

        if (is_array($data))
        {
            foreach ($data as &$v)
                $v = self::jsEscape($v);

            return $data;
        }

        return strtr($data, array(
            '/'  => '\/',
            '\\' => '\\\\',
            "'"  => "\\'",
            '"'  => '\\"',
            "\r" => '\\r',
            "\n" => '\\n'
        ));
    }

    public static function defStatic(array|string $data) : array|string
    {
        if (is_array($data))
        {
            foreach ($data as &$v)
                if ($v)
                    $v = self::defStatic($v);

            return $data;
        }

        return strtr($data, array(
            'HOST_URL'      => Cfg::get('HOST_URL'),
            'STATIC_URL'    => Cfg::get('STATIC_URL'),
            'NAME'          => Cfg::get('NAME'),
            'NAME_SHORT'    => Cfg::get('NAME_SHORT'),
            'CONTACT_EMAIL' => Cfg::get('CONTACT_EMAIL')
        ));
    }

    // default back to enUS if localization unavailable
    public static function localizedString(array $data, string $field, bool $silent = false) : string
    {
        // only display placeholder markers for staff
        if (!User::isInGroup(U_GROUP_EMPLOYEE | U_GROUP_TESTER | U_GROUP_LOCALIZER))
            $silent = true;

        // default case: selected locale available
        if (!empty($data[$field.'_loc'.Lang::getLocale()->value]))
            return $data[$field.'_loc'.Lang::getLocale()->value];

        // locale not enUS; aowow-type localization available; add brackets if not silent
        else if (Lang::getLocale() != Locale::EN && !empty($data[$field.'_loc0']))
            return $silent ? $data[$field.'_loc0'] : '['.$data[$field.'_loc0'].']';

        // locale not enUS; TC localization; add brackets if not silent
        else if (Lang::getLocale() != Locale::EN && !empty($data[$field]))
            return $silent ? $data[$field] : '['.$data[$field].']';

        // locale enUS; TC localization; return normal
        else if (Lang::getLocale() == Locale::EN && !empty($data[$field]))
            return $data[$field];

        // nothing to find; be empty
        else
            return '';
    }

    // for item and spells
    public static function setRatingLevel(int $level, int $statId, int $val, bool $interactive = false) : string
    {
        if (in_array($statId, [Stat::DEFENSE_RTG, Stat::DODGE_RTG, Stat::PARRY_RTG, Stat::BLOCK_RTG, Stat::RESILIENCE_RTG]) && $level < 34)
            $level = 34;

        $factor = Stat::getRatingPctFactor($statId);
        if (!$factor)
            $result = 0;
        else
        {
            if ($level > 70)
                $c = 82 / 52 * pow(131 / 63, ($level - 70) / 10);
            else if ($level > 60)
                $c = 82 / (262 - 3 * $level);
            else if ($level > 10)
                $c = ($level - 8) / 52;
            else
                $c = 2 / 52;

            // do not use localized number format here!
            $result = number_format($val / $factor / $c, 2);
        }

        if (!in_array($statId, [Stat::DEFENSE_RTG, Stat::EXPERTISE_RTG]))
            $result .= '%';

        $result = Lang::item('ratingString', [$statId, $result, $level]);

        return $interactive ? sprintf(self::$setRatingLevelString, $level, $statId, $val, $result) : $result;
    }

    // default ucFirst doesn't convert UTF-8 chars (php 8.4 finally implemented this .. see ya in 2027)
    public static function ucFirst(string $str) : string
    {
        $first = mb_substr($str, 0, 1);
        $rest  = mb_substr($str, 1);

        return mb_strtoupper($first).$rest;
    }

    public static function ucWords(string $str) : string
    {
        return mb_convert_case($str, MB_CASE_TITLE);
    }

    public static function lower(string $str) : string
    {
        return mb_strtolower($str);
    }

    // doesn't handle scientific notation .. why would you input 3e3 for 3000..?
    public static function checkNumeric(mixed &$data, int $typeCast = NUM_ANY) : bool
    {
        if ($data === null)
            return false;

        if (is_array($data))
        {
            array_walk($data, function(&$x) use($typeCast) { self::checkNumeric($x, $typeCast); });
            return false;                                   // always false for passed arrays
        }

        // already in required state
        if ((is_float($data) && $typeCast == NUM_REQ_FLOAT) ||
            (is_int($data) && $typeCast == NUM_REQ_INT))
            return true;

        // irreconcilable state
        if ((!is_int($data) && $typeCast == NUM_REQ_INT) ||
            (!is_float($data) && $typeCast == NUM_REQ_FLOAT))
            return false;

        $number   = $data;                                  // do not transform strings, store state
        $nMatches = 0;

        $number = trim($number);
        $number = preg_replace('/^(-?\d*)[.,](\d+)$/', '$1.$2', $number, -1, $nMatches);

        // is float string
        if ($nMatches)
        {
            if ($typeCast == NUM_CAST_INT)
                $data = intVal($number);
            else                                            // NUM_CAST_FLOAT || NUM_ANY
                $data = floatVal($number);

            return true;
        }

        // is int string (is_numeric can only handle strings in base 10)
        if (is_numeric($number) || preg_match('/^0[xb]?\d+/', $number))
        {
            $number = intVal($number, 0);                   // 'base 0' auto-detects base
            if ($typeCast == NUM_CAST_FLOAT)
                $data = floatVal($number);
            else                                            // NUM_CAST_INT || NUM_ANY
                $data = $number;

            return true;
        }

        // is string string
        return false;
    }

    public static function arraySumByKey(array &$ref, array ...$adds) : void
    {
        if (!$adds)
            return;

        foreach ($adds as $arr)
        {
            foreach ($arr as $k => $v)
            {
                if (!isset($ref[$k]))
                {
                    if (is_array($v))
                        $ref[$k] = [];
                    else if (is_numeric($v))
                        $ref[$k] = 0;
                    else
                        continue;
                }

                if (is_array($ref[$k]) && is_array($v))
                    Util::arraySumByKey($ref[$k], $v);
                else if (is_numeric($ref[$k]) && is_numeric($v))
                    $ref[$k] += $v;
            }
        }
    }

    public static function createNumRange(int $min, int $max, string $delim = '', ?callable $fn = null) : string
    {
        if (!$min && !$max)
            return '';

        $fn ??= fn($x) => $x;
        $_min = $fn($min);
        $_max = $fn($max);

        return $max > $min ? $_min . ($delim ?: Lang::game('valueDelim')) . $_max : $_min;
    }

    public static function validateLogin(?string $val) : string
    {
        if ($_ = self::validateEmail($val))
            return $_;
        if ($_ = self::validateUsername($val))
            return $_;

        return '';
    }

    public static function validateUsername(?string $name, ?int &$errCode = 0) : string
    {
        if (is_null($name) || $name === '')
            return '';

        $errCode   = 0;
        $nameMatch = [];
        [$min, $max, $pattern] = match(Cfg::get('ACC_AUTH_MODE'))
        {
            AUTH_MODE_SELF  => [4, 16, '/^[a-z0-9]{4,16}$/i'],
            AUTH_MODE_REALM => [3, 32, '/^[^[:cntrl:]]+$/'],// i don't think TC has character requirements on the login..?
            default         => [0,  0, '/^[^[:cntrl:]]+$/'] // external case with unknown requirements
        };

        if (($min && mb_strlen($name) < $min) || ($max && mb_strlen($name) > $max))
            $errCode = 1;
        else if ($pattern && !preg_match($pattern, trim(urldecode($name)), $nameMatch))
            $errCode = 2;

        return $errCode ? '' : ($nameMatch[0] ?: $name);
    }

    public static function validatePassword(?string $pass, ?int &$errCode = 0) : string
    {
        if (is_null($pass) || $pass === '')
            return '';

        $errCode   = 0;
        $passMatch = '';
        [$min, $max, $pattern] = match(Cfg::get('ACC_AUTH_MODE'))
        {
            AUTH_MODE_SELF  => [6, 0, '/^[^[:cntrl:]]+$/'],
            AUTH_MODE_REALM => [0, 0, '/^[^[:cntrl:]]+$/'],
            default         => [0, 0, '/^[^[:cntrl:]]+$/']
        };

        if (($min && mb_strlen($pass) < $min) || ($max && mb_strlen($pass) > $max))
            $errCode = 1;
        else if ($pattern && !preg_match($pattern, $pass, $passMatch))
            $errCode = 2;

        return $errCode ? '' : ($passMatch[0] ?: $pass);
    }

    public static function validateEmail(?string $email) : string
    {
        if (is_null($email) || $email === '')
            return '';

        if (preg_match('/^([a-z0-9._-]+)(\+[a-z0-9._-]+)?(@[a-z0-9.-]+\.[a-z]{2,4})$/i', urldecode(trim($email)), $m))
            return $m[0];

        return '';
    }

    public static function loadStaticFile($file, &$result, $localized = false)
    {
        $success = true;
        if ($localized)
        {
            if (file_exists('datasets/'.Lang::getLocale()->json().'/'.$file))
                $result .= file_get_contents('datasets/'.Lang::getLocale()->json().'/'.$file);
            else if (file_exists('datasets/enus/'.$file))
                $result .= file_get_contents('datasets/enus/'.$file);
            else
                $success = false;
        }
        else
        {
            if (file_exists('datasets/'.$file))
                $result .= file_get_contents('datasets/'.$file);
            else
                $success = false;
        }

        return $success;
    }

    // just some random numbers for unsafe identification purpose
    public static function createHash(int $length = 40) : string
    {
        static $seed = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
        $hash = '';

        for ($i = 0; $i < $length; $i++)
            $hash .= substr($seed, mt_rand(0, 61), 1);

        return $hash;
    }

    public static function mergeJsGlobals(array &$master, array ...$adds) : bool
    {
        if (!$adds)                                         // insufficient args
            return false;

        foreach ($adds as $arr)
        {
            foreach ($arr as $type => $data)
            {
                // bad data or empty
                if (!Type::exists($type) || !is_array($data) || !$data)
                    continue;

                if (!isset($master[$type]))
                    $master[$type] = [];

                foreach ($data as $k => $d)
                {
                    if (!isset($master[$type][$k]))         // int: id, yet to look up
                        $master[$type][$k] = $d;
                    else if (is_array($d))                  // array: already fetched data (overwrite old value if set)
                        $master[$type][$k] = $d;
                    // else                                 // id overwrites data .. do not want
                }
            }
        }

        return true;
    }

    public static function gainSiteReputation($user, $action, $miscData = [])
    {
        if (!$user || !$action)
            return false;

        $x = [];

        switch ($action)
        {
            case SITEREP_ACTION_REGISTER:
                $x['amount'] = Cfg::get('REP_REWARD_REGISTER');
                break;
            case SITEREP_ACTION_DAILYVISIT:
                $x['sourceA'] = time();
                $x['amount']  = Cfg::get('REP_REWARD_DAILYVISIT');
                break;
            case SITEREP_ACTION_COMMENT:
                if (empty($miscData['id']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['amount']  = Cfg::get('REP_REWARD_COMMENT');
                break;
            case SITEREP_ACTION_UPVOTED:
            case SITEREP_ACTION_DOWNVOTED:
                if (empty($miscData['id']) || empty($miscData['voterId']))
                    return false;

                DB::Aowow()->query(                         // delete old votes the user has cast
                    'DELETE FROM ?_account_reputation WHERE sourceA = ?d AND sourceB = ?d AND userId = ?d AND action IN (?a)',
                    $miscData['id'],
                    $miscData['voterId'],
                    $user,
                    [SITEREP_ACTION_UPVOTED, SITEREP_ACTION_DOWNVOTED]
                );

                $x['sourceA'] = $miscData['id'];            // commentId
                $x['sourceB'] = $miscData['voterId'];
                $x['amount']  = $action == SITEREP_ACTION_UPVOTED ? Cfg::get('REP_REWARD_UPVOTED') : Cfg::get('REP_REWARD_DOWNVOTED');
                break;
            case SITEREP_ACTION_SUBMIT_SCREENSHOT:
            case SITEREP_ACTION_SUGGEST_VIDEO:
                if (empty($miscData['id']) || empty($miscData['what']))
                    return false;

                $x['sourceA'] = $miscData['id'];            // screenshotId or videoId
                $x['amount']  = $action == SITEREP_ACTION_SUBMIT_SCREENSHOT ? Cfg::get('REP_REWARD_SUBMIT_SCREENSHOT') : Cfg::get('REP_REWARD_SUGGEST_VIDEO');
                break;
            case SITEREP_ACTION_GOOD_REPORT:                // NYI
            case SITEREP_ACTION_BAD_REPORT:
                if (empty($miscData['id']))                 // reportId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_GOOD_REPORT ? Cfg::get('REP_REWARD_GOOD_REPORT') : Cfg::get('REP_REWARD_BAD_REPORT');
                break;
            case SITEREP_ACTION_ARTICLE:
                if (empty($miscData['id']))                 // guideId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = Cfg::get('REP_REWARD_ARTICLE');
                break;
            case SITEREP_ACTION_USER_WARNED:                // NYI
            case SITEREP_ACTION_USER_SUSPENDED:
                if (empty($miscData['id']))                 // banId
                    return false;

                $x['sourceA'] = $miscData['id'];
                $x['amount']  = $action == SITEREP_ACTION_USER_WARNED ? Cfg::get('REP_REWARD_USER_WARNED') : Cfg::get('REP_REWARD_USER_SUSPENDED');
                break;
        }

        $x = array_merge($x, array(
            'userId' => $user,
            'action' => $action,
            'date'   => !empty($miscData['date']) ? $miscData['date'] : time()
        ));

        return DB::Aowow()->query('INSERT IGNORE INTO ?_account_reputation (?#) VALUES (?a)', array_keys($x), array_values($x));
    }

    public static function toJSON($data, $forceFlags = 0)
    {
        $flags = $forceFlags ?: (JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        if (Cfg::get('DEBUG') && !$forceFlags)
            $flags |= JSON_PRETTY_PRINT;

        $json = json_encode($data, $flags);

        // handle strings prefixed with $ as js-variables
        // literal: match everything (lazy) between first pair of unescaped double quotes. First character must be $.
        $json = preg_replace_callback('/(?<!\\\\)"\$(.+?)(?<!\\\\)"/i', fn($m) => str_replace('\"', '"', $m[1]), $json);

        return $json;
    }

    public static function createSqlBatchInsert(array $data) : array
    {
        if (!count($data) || !is_array(reset($data)))
            return [];

        $nRows  = 100;
        $nItems = count(reset($data));
        $result = [];
        $buff   = [];

        foreach ($data as $d)
        {
            if (count($d) != $nItems)
                return [];

            $d = array_map(fn($x) => $x === null ? 'NULL' : DB::Aowow()->escape($x), $d);

            $buff[] = implode(',', $d);

            if (count($buff) >= $nRows)
            {
                $result[] = '('.implode('),(', $buff).')';
                $buff = [];
            }
        }

        if ($buff)
            $result[] = '('.implode('),(', $buff).')';

        return $result;
    }

    /*****************/
    /* file handling */
    /*****************/

    public static function writeFile($file, $content)
    {
        $success = false;

        $parentDir = mb_substr($file, 0, mb_strrpos($file, '/'));
        if (!self::writeDir($parentDir))
            return false;

        if ($handle = @fOpen($file, "w"))
        {
            if (fWrite($handle, $content))
                $success = true;
            else
                trigger_error('could not write to file', E_USER_ERROR);

            fClose($handle);
        }
        else
            trigger_error('could not create file', E_USER_ERROR);

        if ($success)
            @chmod($file, self::FILE_ACCESS);

        return $success;
    }

    public static function writeDir(string $dir, bool &$exist = true) : bool
    {
        // remove multiple slashes; trailing slashes
        $dir   = preg_replace(['/\/+/', '/\/$/'], ['/', ''], $dir) ?: '.';
        $exist = is_dir($dir);

        if ($exist)
        {
            if (fileperms($dir) != self::DIR_ACCESS && !@chmod($dir, self::DIR_ACCESS))
                trigger_error(CLI::bold($dir) . ' may be inaccessible to the web service.', E_USER_WARNING);

            return is_writable($dir);
        }

        // apparently chmod can't edit a whole path at once
        $path = '';
        foreach(explode('/', $dir) as $segment)
            if (is_dir($path .= $segment.'/') && fileperms($path) != self::DIR_ACCESS)
                @chmod($path, self::DIR_ACCESS);

        if (@mkdir($dir, self::DIR_ACCESS, true))
            return true;

        trigger_error('could not create directory', E_USER_ERROR);
        return false;
    }


    /**************/
    /* Good Skill */
    /**************/

    public static function getEquipmentScore(int $itemLevel, int $quality, int $slot, int $nSockets = 0) : float
    {
        if ($itemLevel < 0)                                 // can this even happen?
            $itemLevel = 0;

        $score = $itemLevel;

        // quality mod
        switch ($quality)
        {
            case ITEM_QUALITY_POOR:
                $score = 0;                                 // guessed as crap
                break;
            case ITEM_QUALITY_NORMAL:
                $score = 0;                                 // guessed as crap
                break;
            case ITEM_QUALITY_UNCOMMON:
                $score /= 2.0;
                break;
            case ITEM_QUALITY_RARE:
                $score /= 1.8;
                break;
            case ITEM_QUALITY_EPIC:
                $score /= 1.2;
                break;
            case ITEM_QUALITY_LEGENDARY:
                $score /= 1;
                break;
            case ITEM_QUALITY_HEIRLOOM:                     // actual calculation in javascript .. still uses this as some sort of factor..?
                break;
            case ITEM_QUALITY_ARTIFACT:
                break;
        }

        switch ($slot)
        {
            case INVTYPE_WEAPON:
            case INVTYPE_WEAPONMAINHAND:
            case INVTYPE_WEAPONOFFHAND:
                $score *= 27/64;
                break;
            case INVTYPE_SHIELD:
            case INVTYPE_HOLDABLE:
                $score *= 9/16;
                break;
            case INVTYPE_HEAD:
            case INVTYPE_CHEST:
            case INVTYPE_LEGS:
            case INVTYPE_2HWEAPON:
                $score *= 1.0;
                break;
            case INVTYPE_SHOULDERS:
            case INVTYPE_HANDS:
            case INVTYPE_WAIST:
            case INVTYPE_FEET:
                $score *= 3/4;
                break;
            case INVTYPE_WRISTS:
            case INVTYPE_NECK:
            case INVTYPE_CLOAK:
            case INVTYPE_FINGER:
            case INVTYPE_TRINKET:
                $score *= 9/16;
                break;
            case INVTYPE_THROWN:
            case INVTYPE_RANGED:
            case INVTYPE_RELIC:
                $score *= 81/256;
                break;
            default:
                $score *= 0.0;
        }

        // subtract sockets
        if ($nSockets)
        {
            // items by expansion overlap in this range. luckily highlevel raid items are exclusivly epic or better
            if ($itemLevel > 164 || ($itemLevel > 134 && $quality < ITEM_QUALITY_EPIC))
                $score -= $nSockets * self::GEM_SCORE_BASE_WOTLK;
            else
                $score -= $nSockets * self::GEM_SCORE_BASE_BC;
        }

        return round($score, 4);
    }

    public static function getGemScore(int $itemLevel, int $quality, bool $profSpec = false, int $itemId = 0) : float
    {
        // prepare score-lookup
        if (empty(self::$perfectGems))
            self::$perfectGems = DB::World()->selectCol('SELECT perfectItemType FROM skill_perfect_item_template WHERE requiredSpecialization = ?d', 55534);

        // epic - WotLK - increased stats / profession specific (Dragon's Eyes)
        if ($profSpec)
            return 32.0;
        // epic - WotLK - base stats
        if ($itemLevel == 80 && $quality == ITEM_QUALITY_EPIC)
            return 20.0;
        // rare - WotLK [GEM BASELINE!]
        if ($itemLevel == 80 && $quality == ITEM_QUALITY_RARE)
            return 16.0;
        // uncommon - WotLK - inreased stats
        if ($itemId > 0 && in_array($itemId, self::$perfectGems))
            return 14.0;
        // uncommon - WotLK - base stats
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_UNCOMMON)
            return 12.0;
        // epic - BC - vendored (PvP)
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_EPIC)
            return 10.0;
        // epic - BC - dropped / crafted
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_EPIC)
            return 9.0;
        // rare - BC - crafted
        if ($itemLevel == 70 && $quality == ITEM_QUALITY_RARE)
            return 8.0;
        // rare - BC - vendored (pvp)
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_RARE)
            return 7.0;
        // uncommon - BC
        if ($itemLevel == 60 && $quality == ITEM_QUALITY_UNCOMMON)
            return 6.0;
        // common - BC - vendored gems
        if ($itemLevel == 55 && $quality == ITEM_QUALITY_NORMAL)
            return 4.0;

        // dafuq..?
        return 0.0;
    }

    public static function getEnchantmentScore(int $itemLevel, int $quality, bool $profSpec = false, int $idOverride = 0) : float
    {
        if ($itemLevel < 0)                                 // can this even happen?
            $itemLevel = 0;

        // some hardcoded values, that defy lookups (cheaper but not skillbound profession versions of spell threads, leg armor)
        if (in_array($idOverride, [3327, 3328, 3872, 3873]))
            return 20.0;

        if ($profSpec)
            return 40.0;

        // other than the constraints (0 - 20 points; 40 for profession perks), everything in here is guesswork
        $score = min($itemLevel, 80);

        switch ($quality)
        {
            case ITEM_QUALITY_HEIRLOOM:                 // because i say so!
                $score = 20.0;
                break;
            case ITEM_QUALITY_RARE:
                $score /= 4.8;
                break;
            case ITEM_QUALITY_UNCOMMON:
                $score /= 6.4;
                break;
            case ITEM_QUALITY_NORMAL:
                $score /= 10.0;
                break;
            default:
                $score /= 4.0;
        }

        return round($score, 4);
    }

    public static function fixWeaponScores(int $class, array $talents, array $mainHand, array $offHand) : array
    {
        $mh = 1;
        $oh = 1;

        if ($mainHand) { // Main Hand Equipped
            if ($offHand) { // Off Hand Equipped
                if ($mainHand['slotbak'] == 21 || $mainHand['slotbak'] == 13) { // Main Hand, One Hand
                    if ($offHand['slotbak'] == 22 || $offHand['slotbak'] == 13) { // Off Hand, One Hand
                        if ($class == 6 || $class == 3 || $class == 4 || // Death Knight, Hunter, Rogue
                           ($class == 7 && $talents['spent'][1] > 30 && $talents['spec'] == 2) || // Enhancement Shaman Over 39
                           ($class == 1 && $talents['spent'][1] < 51 && $talents['spec'] == 2)) // Fury Warrior Under 60
                        {
                            $mh = 64 / 27;
                            $oh = 64 / 27;
                        }
                    }
                    else if ($offHand['slotbak'] == 23 || $offHand['slotbak'] == 14) { // Held in Off Hand, Shield
                        if ($class == 5 || $class == 9 || $class == 8 || // Priest, Warlock, Mage
                           ($class == 11 && ($talents['spec'] == 1 || $talents['spec'] == 3)) || // Balance Druid, Restoration Druid
                           ($class == 7 && ($talents['spec'] == 1 || $talents['spec'] == 3)) || // Elemental Shaman, Restoration Shaman
                           ($class == 2 && ($talents['spec'] == 1 || $talents['spec'] == 2)) || // Holy Paladin, Protection Paladin
                           ($class == 1 && $talents['spec'] == 3))  // Protection Warrior
                        {
                            $mh = 64 / 27;
                            $oh = 16 / 9;
                        }
                    }
                }
            }
            else if ($mainHand['slotbak'] == 17) {  // Two Handed
                if ($class == 5 || $class == 9 || $class == 8 || // Priest, Warlock, Mage
                    $class == 11 || $class == 3 || $class == 6 || // Druid, Hunter, Death Knight
                   ($class == 7 && $talents['spent'][1] < 31 && $talents['spec'] == 2) || // Enhancement Shaman Under 40
                   ($class == 2 && $talents['spec'] == 3) || // Retribution Paladin
                   ($class == 1 && $talents['spec'] == 1)) // Arms Warrior
                {
                    $mh = 2;
                    $oh = 0;
                }
            }
        }

        return array(
            round(($mainHand['gearscore'] ?? 0) * $mh),
            round(($offHand['gearscore']  ?? 0) * $oh)
        );
    }

    // orientation is 2*M_PI for a full circle, increasing counterclockwise
    public static function O2Deg($o)
    {
        // orientation values can exceed boundaries (for whatever reason)
        while ($o < 0)
            $o += 2*M_PI;

        while ($o >= 2*M_PI)
            $o -= 2*M_PI;

        $deg = 360 * (1 - ($o / (2*M_PI) ) );
        if ($deg == 360)
            $deg = 0;

        $dir  = Lang::game('orientation');
        $desc = '';
        foreach ($dir as $f => $d)
        {
            if (!$f)
                continue;

            if ( ($deg >= (45 * $f) - 22.5) && ($deg <= (45 * $f) + 22.5) )
            {
                $desc = $d;
                break;
            }
        }

        if (!$desc)
            $desc = $dir[0];

        return [(int)$deg, $desc];
    }

    public static function mask2bits(int $bitmask, int $offset = 0) : array
    {
        $bits = [];
        $i    = 0;
        while ($bitmask)
        {
            if ($bitmask & (1 << $i))
            {
                $bitmask &= ~(1 << $i);
                $bits[] = ($i + $offset);
            }
            $i++;
        }

        return $bits;
    }

    public static function indexBitBlob(string $bitBlob, int $blobSize = 32) : array
    {
        $indizes = [];
        $blocks  = explode(' ', trim($bitBlob));
        for ($i = 0; $i < count($blocks); $i++)
            for ($j = 0; $j < $blobSize; $j++)
                if ($blocks[$i] & (1 << $j))
                    $indizes[] = $j + ($i * $blobSize);

        return $indizes;
    }

    public static function toString(mixed $var) : string
    {
        if (is_array($var))
            return '[' . implode(', ', array_map(self::toString(...), $var)) . ']';

        if (is_object($var))
        {
         // hm, respect object stringability?
         // if ($var instanceof Stringable)
         //     return (string)$var;

            $buff = [];
            foreach ($var as $k => $v)
                $buff[] = $k.':'.self::toString($v);

            return '{' . implode(', ', $buff) . '}';
        }

        return (string)$var;
    }

    public static function nodeAttributes(?array $attribs) : string
    {
        if (!$attribs)
            return '';

        return array_reduce(array_keys($attribs), fn($carry, $name) => $carry . match(gettype($attribs[$name]))
            {
                'boolean'  => ' ' . $attribs[$name] ? $name : '',
                'integer',
                'double'   => ' ' . $name . '="' . $attribs[$name] . '"',
                'string'   => ' ' . $name . '="' . self::htmlEscape($attribs[$name]) . '"',
                'array'    => ' ' . $name . '="' . implode(' ', self::htmlEscape($attribs[$name])) . '"',
                default    => ''
            }, '');
    }

    public static function buildPosFixMenu(int $mapId, float $posX, float $posY, int $type, int $guid, int $parentArea = 0, int $parentFloor = 0) : array
    {
        $points = WorldPosition::toZonePos($mapId, $posX, $posY);
        if (!$points || count($points) < 2)
            return [];

        $floors = [];
        $menu   = [[null, "Move Location to..."]];
        foreach ($points as $p)
        {
            if ($p['multifloor'])
                $floors[$p['areaId']][] = $p['floor'];

            if (isset($menu[$p['areaId']]))
                continue;
            else if ($p['areaId'] == $parentArea)
                $menu[$p['areaId']] = [$p['areaId'], '$g_zones['.$p['areaId'].']', '', null, ['class' => 'checked q0']];
            else
                $menu[$p['areaId']] = [$p['areaId'], '$g_zones['.$p['areaId'].']', '$spawnposfix.bind(null, '.$type.', '.$guid.', '.$p['areaId'].', 0)', null, null];
        }

        foreach ($floors as $area => $f)
        {
            $menu[$area][MENU_IDX_URL] = null;
            $menu[$area][MENU_IDX_SUB] = [];
            if ($menu[$area][MENU_IDX_OPT])
                $menu[$area][MENU_IDX_OPT]['class'] = 'checked';

            foreach ($f as $n)
            {
                if ($n == $parentFloor)
                    $menu[$area][MENU_IDX_SUB][] = [$n, '$g_zone_areas['.$area.']['.($n - 1).']', '', null, ['class' => 'checked q0']];
                else
                    $menu[$area][MENU_IDX_SUB][] = [$n, '$g_zone_areas['.$area.']['.($n - 1).']', '$spawnposfix.bind(null, '.$type.', '.$guid.', '.$area.', '.$n.')'];
            }
        }

        return array_values($menu);
    }

    public static function sendMail(string $email, string $tplFile, array $vars = [], int $expiration = 0) : bool
    {
        if (!self::validateEmail($email))
            return false;

        $template = '';
        if (file_exists('template/mails/'.$tplFile.'_'.User::$preferedLoc->value.'.tpl'))
            $template = file_get_contents('template/mails/'.$tplFile.'_'.User::$preferedLoc->value.'.tpl');
        else
        {
            foreach (Locale::cases() as $l)
            {
                if (!$l->validate() || !file_exists('template/mails/'.$tplFile.'_'.$l->value.'.tpl'))
                    continue;

                $template = file_get_contents('template/mails/'.$tplFile.'_'.$l->value.'.tpl');
                break;
            }
        }

        if (!$template)
        {
            trigger_error('Util::SendMail() - mail template not found: '.$tplFile, E_USER_ERROR);
            return false;
        }

        [, $subject, $body] = explode("\n", $template, 3);

        $body = Util::defStatic($body);

        if ($expiration)
        {
            $vars += array_fill(0, 9, null);                // vsprintf requires all unused indizes to also be set...
            $vars[9] = DateTime::formatTimeElapsed($expiration * 1000, 0);
        }

        if ($vars)
            $body = vsprintf($body, $vars);

        $subject = Cfg::get('NAME_SHORT').Lang::main('colon') . $subject;
        $header  = 'From: '         . Cfg::get('CONTACT_EMAIL') . "\n" .
                   'Reply-To: '     . Cfg::get('CONTACT_EMAIL') . "\n" .
                   'X-Mailer: PHP/' . phpversion();

        if (Cfg::get('DEBUG') >= LOG_LEVEL_INFO)
        {
            Util::addNote("Redirected from Util::sendMail:\n\nTo: " . $email . "\n\nSubject: " . $subject . "\n\n" . $body, U_GROUP_NONE, LOG_LEVEL_INFO);
            return true;
        }

        return mail($email, $subject, $body, $header);
    }
}

?>
