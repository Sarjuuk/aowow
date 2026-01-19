<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die("illegal access");


trait TrProfilerFilter
{
    protected array $parentCats = [];                       // used to validate ty-filter

    protected function cbRegionCheck(string &$v) : bool
    {
        if (in_array($v, Util::$regions))
        {
            $this->parentCats[0] = $v;                      // directly redirect onto this region
            $v = '';                                        // remove from filter

            return true;
        }

        return false;
    }

    protected function cbServerCheck(string &$v) : bool
    {
        foreach (Profiler::getRealms() as $realm)
            if (Profiler::urlize($realm['name'], true) == $v)
            {
                $this->parentCats[1] = $v;                  // directly redirect onto this server
                $v = '';                                    // remove from filter

                return true;
            }

        return false;
    }
}

abstract class Filter
{
    private static  $wCards = ['*' => '%', '?' => '_'];

    public const CR_BOOLEAN   = 1;
    public const CR_FLAG      = 2;
    public const CR_NUMERIC   = 3;
    public const CR_STRING    = 4;
    public const CR_ENUM      = 5;
    public const CR_STAFFFLAG = 6;
    public const CR_CALLBACK  = 7;
    public const CR_NYI_PH    = 999;

    public const V_EQUAL      = 8;
    public const V_RANGE      = 9;
    public const V_LIST       = 10;
    public const V_CALLBACK   = 11;
    public const V_REGEX      = 12;

    protected const ENUM_ANY     = -2323;
    protected const ENUM_NONE    = -2324;

    protected const PATTERN_NAME  = '/[\p{C};%\\\\]/ui';
    protected const PATTERN_CRV   = '/[\p{C};:%\\\\]/ui';
    protected const PATTERN_INT   = '/\D/';
    public    const PATTERN_PARAM = '/^[\p{L}\p{Sm} \d\p{P}]+$/ui';
    public    const PATTERN_FT    = '/[^[:alpha:] \d_-]/iu'; // +-*<>@()~" have special meaning; ' seems to fuck up the search; other irregular cases?

    protected const ENUM_FACTION       = array(  469,  1037,  1106,   529,  1012,    87,    21,   910,   609,   942,   909,   530,    69,   577,   930,  1068,  1104,   729,   369,    92,
                                                  54,   946,    67,  1052,   749,    47,   989,  1090,  1098,   978,  1011,    93,  1015,  1038,    76,   470,   349,  1031,  1077,   809,
                                                 911,   890,   970,   169,   730,    72,    70,   932,  1156,   933,   510,  1126,  1067,  1073,   509,   941,  1105,   990,   934,   935,
                                                1094,  1119,  1124,  1064,   967,  1091,    59,   947,    81,   576,   922,    68,  1050,  1085,   889,   589,   270);
    protected const ENUM_CURRENCY      = array(32572, 32569, 29736, 44128, 20560, 20559, 29434, 37829, 23247, 44990, 24368, 43016, 41596, 34052, 45624, 49426, 40752, 47241, 40753, 29024,
                                               24245, 26045, 26044, 38425, 29735, 24579, 24581, 32897, 22484,  4291, 28558, 43228, 34664, 37836, 20558, 34597, 43589);
    protected const ENUM_EVENT         = array(  372,   283,   285,   353,   420,   400,   284,   201,   374,   409,   141,   324,   321,   424,   423,   327,   341,  181,   404,    398,
                                                 301);
    protected const ENUM_ZONE          = array(   36,    45,     3,     4,    46,    41,  2257,     1,    10,   139,    12,  3430,  3433,   267,  1537,  4080,    38,  4298,    44,    51,
                                                3487,   130,  1519,    33,     8,    47,    85,  1497,    28,    40,    11,   331,    16,  3524,  3525,   148,  1657,   405,    14,    15,
                                                 361,   357,   493,   215,  1637,  1377,   406,   440,   141,    17,  3557,   400,  1638,   490,   618,  4494,  3790,  4277,   719,  1584,
                                                1583,  3713,  1581,  2557,  4196,   721,  4416,  4272,  4820,  4264,  3562,  4131,  3792,  2100,  2367,  4813,  2437,   722,   491,   796,
                                                2057,  3791,  3789,   209,  3714,  3717,   717,  2017,  1477,  3848,  2366,  3847,  4100,  4809,  3717,  3849,  4265,  4228,  3715,  4723,
                                                1337,  3716,   206,  1196,  4415,   718,  1176,  3428,  3959,  2677,  3923,  4812,  3457,  3836,  2717,  3456,  2159,  3429,  3607,  3845,
                                                3606,  4500,  4493,  4987,  4075,  4722,  4273,  4603,  3805,  1977,  2597,  3358,  3820,  4710,  4384,  3277,  3522,  3483,  3518,  3523,
                                                3520,  3703,  3519,  3521,  3702,  4378,  3698,  3968,  4406,  3537,  2817,  4395,    65,   394,   495,  4742,   210,  3711,    67,  4197,
                                                  66);
    protected const ENUM_HEROICDUNGEON = array( 4494,  3790,  4277,  4196,  4416,  4272,  4820,  4264,  3562,  4131,  3792,  2367,  4813,  3791,  3789,  3848,  2366,  3713,  3847,  4100,
                                                4809,  3849,  4265,  4228,  3714,  3717,  3715,  3716,  4415,  4723,  206,   1196);
    protected const ENUM_MULTIMODERAID = array( 4812,  3456,  2159,  4500,  4493,  4722,  4273,  4603,  4987);
    protected const ENUM_HEROICRAID    = array( 4987,  4812,  4722);
    protected const ENUM_CLASSS        = array( null,     1,     2,     3,     4,     5,     6,     7,     8,     9,  null,    11,  true, false);
    protected const ENUM_RACE          = array( null,     1,     2,     3,     4,     5,     6,     7,     8,  null,    10,    11,  true, false);
    protected const ENUM_PROFESSION    = array( null,   171,   164,   185,   333,   202,   129,   755,   165,   186,   197,  true, false,   356,   182,   773);

    public bool  $error        = false;
    public bool  $shouldReload = false;                     // erroneous params have been corrected. Build GET string and reload

    // item related
    public array $upgrades     = [];                        // [itemId => slotId]
    public array $extraOpts    = [];                        // score for statWeights
    public array $wtCnd        = [];                        // DBType condition for statWeights

    private array $cndSet  = [];                            // db type query storage
    private array $rawData = [];

    /* genericFilter: [FILTER_TYPE, colOrFnName, param1, param2]
        [self::CR_BOOLEAN,   <string:colName>, <bool:isString>, null]
        [self::CR_FLAG,      <string:colName>, <int:testBit>,   <bool:matchAny>]       # default param2: matchExact
        [self::CR_NUMERIC,   <string:colName>, <int:NUM_FLAGS>, <bool:addExtraCol>]
        [self::CR_STRING,    <string:colName>, <int:STR_FLAGS>, null]
        [self::CR_ENUM,      <string:colName>, <bool:ANY_NONE>, <bool:isEnumVal>]      # param3 ? crv is val in enum : key in enum
        [self::CR_STAFFFLAG, <string:colName>, null,            null]
        [self::CR_CALLBACK,  <string:fnName>,  <mixed:param1>,  <mixed:param2>]
        [self::CR_NYI_PH,    null,             <int:returnVal>, param2]                # mostly 1: to ignore this criterium; 0: to fail the whole query
    */
    protected string $type          = '';                   // set by child
    protected array  $parentCats    = [];                   // used to validate ty-filter

    protected static array $genericFilter = [];
    protected static array $inputFields   = [];             // list of input fields defined per page - fieldName => [checkType, checkValue[, fieldIsArray]]
    protected static array $enums         = [];             // validation for opt lists per page - criteriumID => [validOptionList]

    // express Filters in template
    public string $fiInit           = '';                   // str: filter template (and init html form)
    public string $fiType           = '';                   // str: filter template (set without init)
    public array  $fiSetCriteria    = [];                   // fn params (cr, crs, crv)
    public array  $fiSetWeights     = [];                   // fn params (weights, nt, ids, stealth)
    public array  $fiReputationCols = [];                   // fn params ([[factionId, factionName], ...])
    public array  $fiExtraCols      = [];                   //
    public string $query            = '';                   // as in url query params
    public array  $values           = [];                   // prefiltered rawData

    // parse the provided request into a usable format
    public function __construct(string|array $data, array $opts = [])
    {
        $this->parentCats = $opts['parentCats'] ?? [];

        // use fn fi_init() if we have a criteria selector, else use var fi_type
        if (static::$genericFilter)
            $this->fiInit = $this->type;
        else
            $this->fiType = $this->type;

        if (is_array($data))
            $this->rawData = $data;                         // could set >query for consistency sake, but is not used when converting from POST

        if (is_string($data))
        {
            // an error occured, while processing POST
            if (isset($_SESSION['error']['fi']))
            {
                $this->error = $_SESSION['error']['fi'] == get_class($this);
                unset($_SESSION['error']['fi']);
            }

            $this->query   = $data;
            $this->rawData = $this->transformGET($data);
        }

        $this->initFields();
        $this->evalCriteria();
        $this->evalWeights();
    }

    public function mergeCat(array &$cats) : void
    {
        foreach ($this->parentCats as $idx => $cat)
            $cats[$idx] = $cat;
    }

    private function &criteriaIterator() : \Generator
    {
        if (empty($this->values['cr']))
            return;

        for ($i = 0; $i < count($this->values['cr']); $i++)
        {
            // throws a notice if yielded directly "Only variable references should be yielded by reference"
            $v = [&$this->values['cr'][$i], &$this->values['crs'][$i], &$this->values['crv'][$i]];
            yield $i => $v;
        }
    }

    public static function getCriteriaIndex(int $cr, int|bool $lookup) : ?int
    {
        // can't use array_search() as bools are valid enum content
        foreach (static::$enums[$cr] ?? [] as $k => $v)
            if ($v === $lookup)
                return $k;
        return null;
    }


    /***********************/
    /* get prepared values */
    /***********************/

    public function buildGETParam(array $override = [], array $addCr = []) : string
    {
        $get = [];
        foreach (array_merge($this->values, $override) as $k => $v)
        {
            if (isset($addCr[$k]))
            {
                $v = $v ? array_merge((array)$v, (array)$addCr[$k]) : $addCr[$k];
                unset($addCr[$k]);
            }

            if ($v === '' || $v === null || $v === [])
                continue;

            $get[$k] = $k.'='.(is_array($v) ? implode(':', $v) : $v);
        }

        // no criteria were set, so no merge occured .. append
        if ($addCr)
        {
            $get['cr']  = 'cr='.$addCr['cr'];
            $get['crs'] = 'crs='.$addCr['crs'];
            $get['crv'] = 'crv='.$addCr['crv'];
        }

        return implode(';', $get);
    }

    public function getConditions() : array
    {
        if (!$this->cndSet)
        {
            // values
            $this->cndSet = $this->createSQLForValues();

            // criteria
            $filters = [];
            foreach ($this->criteriaIterator() as $_cr)
                if ($cnd = $this->createSQLForCriterium(...$_cr))
                    $filters[] = $cnd;

            if ($filters)                                   // if a filter uses criteria it must have a [ma]tch selector
            {
                $filters[] = empty($this->values['ma']) ? 'AND' : 'OR';
                $this->cndSet[] = $filters;
            }
        }

        if ($this->cndSet)
            array_unshift($this->cndSet, 'AND');

        return $this->cndSet;
    }

    public function getSetCriteria(int ...$cr) : array
    {
        if (!$cr || empty($this->values['cr']))
            return [];

        return array_values(array_intersect($this->values['cr'], $cr));
    }


    /**********************/
    /* input sanitization */
    /**********************/

    private function transformGET(string $get) : array
    {
        if (!$get)
            return [];

        $data = [];

        // someone copy/pasted a WH filter
        $get = preg_replace('/^(\d+(:\d+)*);(\d+(:\d+)*);(\P{C}+(:\P{C}+)*)$/', 'cr=$1;crs=$3;crv=$5', $get);

        foreach (explode(';', $get) as $field)
        {
            if (!strstr($field, '='))
            {
                trigger_error('Filter::transformGET - malformed GET string', E_USER_NOTICE);
                $this->error =
                $this->shouldReload = true;
                continue;
            }

            [$k, $v] = explode('=', $field);

            if (!isset(static::$inputFields[$k]))
            {
                trigger_error('Filter::transformGET - GET param not in filter: '.$k, E_USER_NOTICE);
                $this->error =
                $this->shouldReload = true;
                continue;
            }

            $asArray = static::$inputFields[$k][2];

            $data[$k] = $asArray ? explode(':', $v) : $v;
        }

        return $data;
    }

    private function initFields() : void
    {
        /* quirks:
         * - in the POST step there may be excess criteria selectors with a value of '', as unselecting a criteria that is not the last will not remove the row from the UI
         * - if there are no criteria selected, the placeholder selection will always be sent as ['', null, null], similar to the previous quirk
         *
         * same for stat weights on ItemListFilter
         */
        if (!empty($this->rawData['cr']))
            $this->rawData['cr'] = array_filter($this->rawData['cr'], fn($x) => $x !== '') ?: null;

        if (!empty($this->rawData['wt']))
            $this->rawData['wt'] = array_filter($this->rawData['wt'], fn($x) => $x !== '') ?: null;

        $cleanupCr = [];
        foreach (static::$inputFields as $inp => [$type, $valid, $asArray])
        {
            if (!isset($this->rawData[$inp]) || $this->rawData[$inp] === '')
            {
                $this->values[$inp] = $asArray ? [] : null;
                continue;
            }

            $val = $this->rawData[$inp];

            if ($asArray)
            {
                $buff = [];
                foreach ((array)$val as $i => $v)           // can be string|int in POST step if only one value present
                {
                    if (in_array($inp, ['cr', 'crs', 'crv']))
                    {
                        if (!$this->checkInput($type, $valid, $v))
                            $cleanupCr[] = $i;
                        $buff[] = $v;                       // always assign, gets removed later as tuple
                    }
                    else if ($this->checkInput($type, $valid, $v))
                       $buff[] = $v;
                }

                $this->values[$inp] = $buff;
            }
            else
                $this->values[$inp] = $this->checkInput($type, $valid, $val) ? $val : null;
        }

        if ($cleanupCr)
        {
            $this->error =
            $this->shouldReload = true;

            foreach (array_unique($cleanupCr) as $i)
                unset($this->values['cr'][$i], $this->values['crs'][$i], $this->values['crv'][$i]);

            $this->values['cr']  = array_values($this->values['cr']);
            $this->values['crs'] = array_values($this->values['crs']);
            $this->values['crv'] = array_values($this->values['crv']);
        }
    }

    private function evalCriteria() : void                  // [cr]iterium, [cr].[s]ign, [cr].[v]alue
    {
        if (empty($this->values['cr']) && empty($this->values['crs']) && empty($this->values['crv']))
            return;

        if (empty($this->values['cr']) || empty($this->values['crs']) || empty($this->values['crv']))
        {
            trigger_error('Filter::evalCriteria - one of cr, crs, crv is missing', E_USER_NOTICE);
            unset($this->values['cr'], $this->values['crs'], $this->values['crv']);

            $this->error =
            $this->shouldReload = true;
            return;
        }

        $_cr  = &$this->values['cr'];
        $_crs = &$this->values['crs'];
        $_crv = &$this->values['crv'];

        if (count($_cr) != count($_crv) || count($_cr) != count($_crs) || count($_cr) > 5 || count($_crs) > 5 /*|| count($_crv) > 5*/)
        {
            // use min provided criterion as basis; 5 criteria at most
            $min = min(5, count($_cr), count($_crv), count($_crs));
            if (count($_cr) > $min)
                array_splice($_cr, $min);

            if (count($_crv) > $min)
                array_splice($_crv, $min);

            if (count($_crs) > $min)
                array_splice($_crs, $min);

            trigger_error('Filter::evalCriteria - cr, crs, crv are imbalanced', E_USER_NOTICE);
            $this->error =
            $this->shouldReload = true;
        }

        for ($i = 0; $i < count($_cr); $i++)
        {
            if (!isset(static::$genericFilter[$_cr[$i]]) || $_crs[$i] === '' || $_crv[$i] === '')
            {
                if ($_crs[$i] === '' || $_crv[$i] === '')
                    trigger_error('Filter::evalCriteria - received malformed criterium ["'.$_cr[$i].'", "'.$_crs[$i].'", "'.$_crv[$i].'"]', E_USER_NOTICE);
                else
                    trigger_error('Filter::evalCriteria - received unhandled criterium: '.$_cr[$i], E_USER_NOTICE);

                unset($_cr[$i], $_crs[$i], $_crv[$i]);

                $this->error =
                $this->shouldReload = true;
                continue;
            }

            [$crType, $colOrFn, $param1, $param2] = array_pad(static::$genericFilter[$_cr[$i]], 4, null);

            // conduct filter specific checks & casts here
            switch ($crType)
            {
                case self::CR_NUMERIC:
                    $_ = $_crs[$i];
                    if (Util::checkNumeric($_crv[$i], $param1) && $this->int2Op($_))
                        continue 2;
                    break;
                case self::CR_BOOLEAN:
                case self::CR_FLAG:
                    $_ = $_crs[$i];
                    if ($this->int2Bool($_))
                        continue 2;
                    break;
                case self::CR_STAFFFLAG:
                    if (User::isInGroup(U_GROUP_EMPLOYEE) && Util::checkNumeric($_crs[$i], NUM_CAST_INT))
                        continue 2;
                    break;
                case self::CR_ENUM:
                    if (Util::checkNumeric($_crs[$i], NUM_CAST_INT) && (
                        (!$param2 && isset(static::$enums[$_cr[$i]][$_crs[$i]])) ||
                        ($param2 && in_array($_crs[$i], static::$enums[$_cr[$i]])) ||
                        ($param1 && ($_crs[$i] == self::ENUM_ANY || $_crs[$i] == self::ENUM_NONE))
                    ))
                        continue 2;
                    break;
                case self::CR_STRING:
                case self::CR_CALLBACK:
                case self::CR_NYI_PH:
                    continue 2;
                default:
                    trigger_error('Filter::evalCriteria - unknown criteria type: '.$crType, E_USER_WARNING);
                    break;
            }

            trigger_error('Filter::evalCriteria - generic check failed ["'.$_cr[$i].'", "'.$_crs[$i].'", "'.$_crv[$i].'"]', E_USER_NOTICE);
            unset($_cr[$i], $_crs[$i], $_crv[$i]);

            $this->error =
            $this->shouldReload = true;
        }

        $this->fiSetCriteria = [$_cr, $_crs, $_crv];
    }

    private function evalWeights() : void
    {
        // both empty: not in use; not an error
        if (empty($this->values['wt']) && empty($this->values['wtv']))
            return;

        // one empty: erroneous manual input?
        if (!$this->values['wt'] || !$this->values['wtv'])
        {
            trigger_error('Filter::setWeights - one of wt, wtv is missing', E_USER_NOTICE);
            unset($this->values['wt'], $this->values['wtv']);

            $this->error =
            $this->shouldReload = true;
            return;
        }

        $_wt  = &$this->values['wt'];
        $_wtv = &$this->values['wtv'];

        $nwt  = count($_wt);
        $nwtv = count($_wtv);

        if ($nwt != $nwtv)
        {
            trigger_error('Filter::setWeights - wt, wtv are imbalanced', E_USER_NOTICE);
            $this->error =
            $this->shouldReload = true;
        }

        if ($nwt > $nwtv)
            array_splice($_wt, $nwtv);
        else if ($nwtv > $nwt)
            array_splice($_wtv, $nwt);

        $this->fiSetWeights = [$_wt, $_wtv];
    }

    protected function checkInput(int $type, mixed $valid, mixed &$val, bool $recursive = false) : bool
    {
        switch ($type)
        {
            case self::V_EQUAL:
                if (gettype($valid) == 'integer')
                    $val = intval($val);
                else if (gettype($valid) == 'double')
                    $val = floatval($val);
                else /* if (gettype($valid) == 'string') */
                    $val = strval($val);

                if ($valid == $val)
                    return true;

                break;
            case self::V_LIST:
                if (!Util::checkNumeric($val, NUM_CAST_INT))
                    return false;

                if (in_array($val, $valid))
                    return true;

                foreach ($valid as $v)
                {
                    if (gettype($v) != 'array')
                        continue;

                    if ($this->checkInput(self::V_RANGE, $v, $val, true))
                        return true;
                }

                break;
            case self::V_RANGE:
                if (Util::checkNumeric($val, NUM_CAST_INT) && $val >= $valid[0] && $val <= $valid[1])
                    return true;

                break;
            case self::V_CALLBACK:
                if ($this->$valid($val))
                    return true;

                break;
            case self::V_REGEX:
                if (!preg_match($valid, $val))
                    return true;

                break;
        }

        if (!$recursive)
        {
            trigger_error('Filter::checkInput - check failed [type: '.$type.' valid: '.Util::toString($valid).' val: '.((string)$val).']', E_USER_NOTICE);
            $this->error = true;
        }

        return false;
    }

    protected function transformToken(string $string, bool $exact) : string
    {
        // escape manually entered _; entering % should be prohibited
        $string = str_replace('_', '\\_', $string);

        // now replace search wildcards with sql wildcards
        $string = strtr($string, self::$wCards);

        return sprintf($exact ? '%s' : '%%%s%%', $string);
    }

    protected function tokenizeString(array $fields, string $string = '', bool $exact = false, bool $allowShort = false) : array
    {
        if (!$string && $this->values['na'])
            $string = $this->values['na'];

        // always allow sub 3 chars for logographic locales
        if (Lang::getLocale()->isLogographic())
            $allowShort = true;

        $qry = [];
        foreach ($fields as $f)
        {
            $sub    = [];
            $tokens = $exact ? [$string] : array_filter(explode(' ', $string));
            foreach ($tokens as $t)
            {
                if ($t[0] == '-' && (mb_strlen($t) > 3 || $allowShort))
                    $sub[] = [$f, $this->transformToken(mb_substr($t, 1), $exact), '!'];
                else if ($t[0] != '-' && (mb_strlen($t) > 2 || $allowShort))
                    $sub[] = [$f, $this->transformToken($t, $exact)];
            }

            // single cnd?
            if (!$sub)
                continue;
            else if (count($sub) > 1)
                array_unshift($sub, 'AND');
            else
                $sub = $sub[0];

            $qry[] = $sub;
        }

        // single cnd?
        if (!$qry)
        {
            trigger_error('Filter::tokenizeString - could not tokenize string: '.$string, E_USER_NOTICE);
            $this->error = true;
        }
        else if (count($qry) > 1)
            array_unshift($qry, 'OR');
        else
            $qry = $qry[0];

        return $qry;
    }

    protected function buildMatchLookup(array $fields, string $string = '', bool $exact = false, bool $allowShort = false) : array
    {
        if (!$string && $this->values['na'])
            $string = $this->values['na'];

        if (Lang::getLocale()->isLogographic() && !Cfg::get('LOGOGRAPHIC_FT_SEARCH'))
            return $this->tokenizeString($fields, $string, $exact, $allowShort);

        $string = trim(preg_replace(self::PATTERN_FT, ' ', $string));
        if (!$string)
            return [];

        // always allow sub 3 chars for logographic locales
        if (Lang::getLocale()->isLogographic())
            $allowShort = true;

        $sub    = [];
        $tokens = $exact ? [$string] : array_filter(explode(' ', $string));
        foreach ($tokens as $t)
        {
            $ex = $t[0] === '-';
            if ($ex)
                $t = mb_substr($t, 1);

            // cant have trailing/leading dashes. FT confuses them for additional modifiers and dies with a syntax error
            // would be an issue for all modifiers, but Filter::PATTERN_FT only allows for - at this point
            $t = preg_replace('/^-+|-+$/', '', $t);

            if ($allowShort || mb_strlen($t) > 2)
                $sub[] = ($ex ? '-' : '+') . $t . '*';
        }

        $qry = [];
        foreach ($fields as $f)
            $qry[] = [$f, $sub, 'MATCH'];

        // single cnd?
        if (!$qry)
        {
            trigger_error('Filter::buildMatchLookup - could build MATCH AGAINST from: '.$string, E_USER_NOTICE);
            $this->error = true;
        }
        else if (count($qry) > 1)
            array_unshift($qry, 'OR');
        else
            $qry = $qry[0];

        return $qry;
    }

    protected function int2Op(mixed &$op) : bool
    {
        $op = match ($op) {
            1       => '>',
            2       => '>=',
            3       => '=',
            4       => '<=',
            5       => '<',
            6       => '!=',
            default => null
        };

        return $op !== null;
    }

    protected function int2Bool(mixed &$op) : bool
    {
        $op = match ($op) {
            1       => true,
            2       => false,
            default => null
        };

        return $op !== null;
    }

    protected function list2Mask(array $list, bool $noOffset = false) : int
    {
        $mask = 0x0;
        $o    = $noOffset ? 0 : 1;                          // schoolMask requires this..?

        foreach ($list as $itm)
            $mask += (1 << (intval($itm) - $o));

        return $mask;
    }


    /**************************/
    /* create conditions from */
    /*    generic criteria    */
    /**************************/

    private function genericBoolean(string $field, int $op, bool $isString) : ?array
    {
        if ($this->int2Bool($op))
        {
            $value    = $isString ? '' : 0;
            $operator = $op ? '!' : null;

            return [$field, $value, $operator];
        }

        return null;
    }

    private function genericBooleanFlags(string $field, int $value, int $op, ?bool $matchAny = false) : ?array
    {
        if (!$this->int2Bool($op))
            return null;

        if (!$op)
            return [[$field, $value, '&'], 0];
        else if ($matchAny)
            return [[$field, $value, '&'], 0, '!'];
        else
            return [[$field, $value, '&'], $value];
    }

    private function genericString(string $field, string $value, ?int $strFlags) : ?array
    {
        $strFlags ??= 0x0;

        if ($strFlags & STR_LOCALIZED)
            $field .= '_loc'.Lang::getLocale()->value;

        return $this->tokenizeString([$field], $value, $strFlags & STR_MATCH_EXACT, $strFlags & STR_ALLOW_SHORT);
    }

    private function genericNumeric(string $field, int|float $value, int $op, int $typeCast) : ?array
    {
        if (!Util::checkNumeric($value, $typeCast))
            return null;

        if ($this->int2Op($op))
            return [$field, $value, $op];

        return null;
    }

    private function genericEnum(string $field, mixed $value) : ?array
    {
        if (is_bool($value))
            return [$field, 0, ($value ? '>' : '<=')];
        else if ($value == self::ENUM_ANY)
            return [$field, 0, '!'];
        else if ($value == self::ENUM_NONE)
            return [$field, 0];
        else if ($value !== null)
            return [$field, $value];

        return null;
    }


    /***********************************/
    /*     create conditions from      */
    /* non-generic values and criteria */
    /***********************************/

    protected function createSQLForCriterium(int $cr, int $crs, string $crv) : array
    {
        if (!static::$genericFilter)                        // criteria not in use - no error
            return [];

        [$crType, $colOrFn, $param1, $param2] = array_pad(static::$genericFilter[$cr], 4, null);

        $handleEnum = function(int $cr, int $crs, string $col, ?bool $hasAnyNone, ?bool $crsAsVal) : ?array
        {
            if ($hasAnyNone && ($crs == self::ENUM_ANY || $crs == self::ENUM_NONE))
                return $this->genericEnum($col, $crs);
            else if (!$crsAsVal && isset(static::$enums[$cr][$crs]))
                return $this->genericEnum($col, static::$enums[$cr][$crs]);
            else if ($crsAsVal && in_array($crs, static::$enums[$cr]))
                return $this->genericEnum($col, $crs);

            return null;
        };

        $handleNYIPH = function(int $crs, string $crv, ?int $forceResult) : ?array
        {
            if (is_int($forceResult))
                return [$forceResult];

            // for nonsensical values; compare against 0
            if ($this->int2Op($crs) && Util::checkNumeric($crv))
            {
                if ($crs == '=')
                    $crs = '==';

                return eval('return ('.$crv.' '.$crs.' 0);') ? [1] : [0];
            }
            else
                return [0];
        };

        $result = match ($crType)
        {
            self::CR_NUMERIC   => $this->genericNumeric($colOrFn, $crv, $crs, $param1),
            self::CR_FLAG      => $this->genericBooleanFlags($colOrFn, $param1, $crs, $param2),
            self::CR_STAFFFLAG => $this->genericBooleanFlags($colOrFn, (1 << ($crs - 1)), true),
            self::CR_BOOLEAN   => $this->genericBoolean($colOrFn, $crs, !empty($param1)),
            self::CR_STRING    => $this->genericString($colOrFn, $crv, $param1),
            self::CR_CALLBACK  => $this->{$colOrFn}($cr, $crs, $crv, $param1, $param2),
            self::CR_ENUM      => $handleEnum($cr, $crs, $colOrFn, $param1, $param2),
            self::CR_NYI_PH    => $handleNYIPH($crs, $crv, $param1),
            default            => null
        };

        if (!$result)
        {
            // this really should not have happened. The relevant checks are run on __construct()
            trigger_error('Filter::createSQLForCriterium - failed to resolve criterium: ["'.$cr.'", "'.$crs.'", "'.$crv.'"]', E_USER_WARNING);
            return [];
        }

        if ($crType == self::CR_NUMERIC && !empty($param2))
            $this->fiExtraCols[] = $cr;

        return $result;
    }

    abstract protected function createSQLForValues() : array;
}

?>
