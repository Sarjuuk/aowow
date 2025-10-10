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
    public    const PATTERN_PARAM = '/^[\p{L}\p{Sm} \d\p{P}]+$/i';

    protected const ENUM_FACTION       = array(  469,  1037,  1106,   529,  1012,    87,    21,   910,   609,   942,   909,   530,    69,   577,   930,  1068,  1104,   729,   369,    92,
                                                  54,   946,    67,  1052,   749,    47,   989,  1090,  1098,   978,  1011,    93,  1015,  1038,    76,   470,   349,  1031,  1077,   809,
                                                 911,   890,   970,   169,   730,    72,    70,   932,  1156,   933,   510,  1126,  1067,  1073,   509,   941,  1105,   990,   934,   935,
                                                1094,  1119,  1124,  1064,   967,  1091,    59,   947,    81,   576,   922,    68,  1050,  1085,   889,   589,   270);
    protected const ENUM_CURRENCY      = array(32572, 32569, 29736, 44128, 20560, 20559, 29434, 37829, 23247, 44990, 24368, 52027, 52030, 43016, 41596, 34052, 45624, 49426, 40752, 47241,
                                               40753, 29024, 24245, 26045, 26044, 38425, 29735, 24579, 24581, 32897, 22484, 52026, 52029,  4291, 28558, 43228, 34664, 47242, 52025, 52028,
                                               37836, 20558, 34597, 43589);
    protected const ENUM_EVENT         = array(  372,   283,   285,   353,   420,   400,   284,   201,   374,   409,   141,   324,   321,   424,   423,   327,   341,  181,   404,    398,
                                                 301);
    protected const ENUM_ZONE          = array( 4494,    36,  2597,  3358,    45,   331,  3790,  4277,    16,  3524,     3,  3959,   719,  1584,    25,  1583,  2677,  3702,  3522,     4,
                                                3525,  3537,    46,  1941,  2918,  3905,  4024,  2817,  4395,  4378,   148,   393,  1657,    41,  2257,   405,  2557,    65,  4196,     1,
                                                  14,    10,    15,   139,    12,  3430,  3820,   361,   357,  3433,   721,   394,  3923,  4416,  2917,  4272,  4820,  4264,  3483,  3562,
                                                 267,   495,  4742,  3606,   210,  4812,  1537,  4710,  4080,  3457,    38,  4131,  3836,  3792,  2100,  2717,   493,   215,  3518,  3698,
                                                3456,  3523,  2367,  2159,  1637,  4813,  4298,  2437,   722,   491,    44,  3429,  3968,   796,  2057,    51,  3607,  3791,  3789,   209,
                                                3520,  3703,  3711,  1377,  3487,   130,  3679,   406,  1519,  4384,    33,  2017,  1477,  4075,     8,   440,   141,  3428,  3519,  3848,
                                                  17,  2366,  3840,  3713,  3847,  3775,  4100,  1581,  3557,  3845,  4500,  4809,    47,  3849,  4265,  4493,  4228,  3698,  4406,  3714,
                                                3717,  3715,   717,    67,  3716,   457,  4415,   400,  1638,  1216,    85,  4723,  4722,  1337,  4273,   490,  1497,   206,  1196,  4603,
                                                 718,  3277,    28,    40,    11,  4197,   618,  3521,  3805,    66,  1176,  1977,  4987);
    protected const ENUM_HEROICDUNGEON = array( 4494,  3790,  4277,  4196,  4416,  4272,  4820,  4264,  3562,  4131,  3792,  2367,  4813,  3791,  3789,  3848,  2366,  3713,  3847,  4100,
                                                4809,  3849,  4265,  4228,  3714,  3717,  3715,  3716,  4415,  4723,  206,   1196);
    protected const ENUM_MULTIMODERAID = array( 4812,  3456,  2159,  4500,  4493,  4722,  4273,  4603,  4987);
    protected const ENUM_HEROICRAID    = array( 4987,  4812,  4722);
    protected const ENUM_CLASSS        = array( null,     1,     2,     3,     4,     5,     6,     7,     8,     9,  null,    11,  true, false);
    protected const ENUM_RACE          = array( null,     1,     2,     3,     4,     5,     6,     7,     8,  null,    10,    11,  true, false);
    protected const ENUM_PROFESSION    = array( null,   171,   164,   185,   333,   202,   129,   755,   165,   186,   197,  true, false,   356,   182,   773);

    public bool  $error     = false;                        // erroneous search fields

    // item related
    public array $upgrades  = [];                           // [itemId => slotId]
    public array $extraOpts = [];                           // score for statWeights
    public array $wtCnd     = [];                           // DBType condition for statWeights

    private array $cndSet  = [];                            // db type query storage
    private array $rawData = [];

    /* genericFilter: [FILTER_TYPE, colOrFnName, param1, param2]
        [self::CR_BOOLEAN,   <string:colName>, <bool:isString>, null]
        [self::CR_FLAG,      <string:colName>, <int:testBit>,   <bool:matchAny>]       # default param2: matchExact
        [self::CR_NUMERIC,   <string:colName>, <int:NUM_FLAGS>, <bool:addExtraCol>]
        [self::CR_STRING,    <string:colName>, <int:STR_FLAGS>, null]
        [self::CR_ENUM,      <string:colName>, <bool:ANYNONE>,  <bool:isEnumVal>]      # param3 ? crv is val in enum : key in enum
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
    public array  $values           = [];                   // old fiData['v']
    public array  $criteria         = [];                   // old fiData['c']

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
    }

    public function mergeCat(array &$cats) : void
    {
        foreach ($this->parentCats as $idx => $cat)
            $cats[$idx] = $cat;
    }

    private function &criteriaIterator() : \Generator
    {
        if (!$this->criteria)
            return;

        for ($i = 0; $i < count($this->criteria['cr']); $i++)
        {
            // throws a notice if yielded directly "Only variable references should be yielded by reference"
            $v = [&$this->criteria['cr'][$i], &$this->criteria['crs'][$i], &$this->criteria['crv'][$i]];
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
        foreach (array_merge($this->criteria, $this->values, $override) as $k => $v)
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
            foreach ($this->criteriaIterator() as &$_cr)
                if ($cnd = $this->createSQLForCriterium(...$_cr))
                    $this->cndSet[] = $cnd;

            if ($this->cndSet)                              // Note: TYPE_SOUND does not use 'match any'
                array_unshift($this->cndSet, empty($this->values['ma']) ? 'AND' : 'OR');
        }

        return $this->cndSet;
    }

    public function getSetCriteria(int ...$cr) : array
    {
        if (!$cr || !$this->fiSetCriteria)
            return $this->fiSetCriteria;

        return array_values(array_intersect($this->fiSetCriteria['cr'], $cr));
    }


    /**********************/
    /* input sanitization */
    /**********************/

    private function transformGET(string $get) : array
    {
        if (!$get)
            return [];

        $data = [];
        foreach (explode(';', $get) as $field)
        {
            if (!strstr($field, '='))
            {
                trigger_error('Filter::transformGET - malformed GET string', E_USER_NOTICE);
                $this->error = true;
                continue;
            }

            [$k, $v] = explode('=', $field);

            if (!isset(static::$inputFields[$k]))
            {
                trigger_error('Filter::transformGET - GET param not in filter: '.$k, E_USER_NOTICE);
                $this->error = true;
                continue;
            }

            $asArray = static::$inputFields[$k][2];

            $data[$k] = $asArray ? explode(':', $v) : $v;
        }

        return $data;
    }

    private function initFields() : void
    {
        foreach (static::$inputFields as $inp => [$type, $valid, $asArray])
        {
            $var = in_array($inp, ['cr', 'crs', 'crv']) ? 'criteria' : 'values';

            if (!isset($this->rawData[$inp]) || $this->rawData[$inp] === '')
            {
                $this->$var[$inp] = $asArray ? [] : null;
                continue;
            }

            $val = $this->rawData[$inp];

            if ($asArray)
            {
                // quirk: in the POST step criteria can be [[''], null, null] if not selected.
                $buff = [];
                foreach ((array)$val as $v)                 // can be string|int in POST step if only one value present
                    if ($v !== '' && $this->checkInput($type, $valid, $v))
                       $buff[] = $v;

                $this->$var[$inp] = $buff;
            }
            else
                $this->$var[$inp] = $this->checkInput($type, $valid, $val) ? $val : null;
        }
    }

    public function evalCriteria() : void                   // [cr]iterium, [cr].[s]ign, [cr].[v]alue
    {
        if (empty($this->criteria['cr']) && empty($this->criteria['crs']) && empty($this->criteria['crv']))
            return;
        else if (empty($this->criteria['cr']) || empty($this->criteria['crs']) || empty($this->criteria['crv']))
        {
            unset($this->criteria['cr']);
            unset($this->criteria['crs']);
            unset($this->criteria['crv']);

            trigger_error('Filter::setCriteria - one of cr, crs, crv is missing', E_USER_NOTICE);
            $this->error = true;
            return;
        }

        $_cr  = &$this->criteria['cr'];
        $_crs = &$this->criteria['crs'];
        $_crv = &$this->criteria['crv'];

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

            trigger_error('Filter::setCriteria - cr, crs, crv are imbalanced', E_USER_NOTICE);
            $this->error = true;
        }

        for ($i = 0; $i < count($_cr); $i++)
        {
            // conduct filter specific checks & casts here
            $unsetme = false;
            if (isset(static::$genericFilter[$_cr[$i]]))
            {
                $gf = static::$genericFilter[$_cr[$i]];
                switch ($gf[0])
                {
                    case self::CR_NUMERIC:
                        $_ = $_crs[$i];
                        if (!Util::checkNumeric($_crv[$i], $gf[2]) || !$this->int2Op($_))
                            $unsetme = true;
                        break;
                    case self::CR_BOOLEAN:
                    case self::CR_FLAG:
                        $_ = $_crs[$i];
                        if (!$this->int2Bool($_))
                            $unsetme = true;
                        break;
                    case self::CR_ENUM:
                    case self::CR_STAFFFLAG:
                        if (!Util::checkNumeric($_crs[$i], NUM_CAST_INT))
                            $unsetme = true;
                        break;
                }
            }

            if (!$unsetme && intval($_cr[$i]) && $_crs[$i] !== '' && $_crv[$i] !== '')
                continue;

            unset($_cr[$i]);
            unset($_crs[$i]);
            unset($_crv[$i]);

            trigger_error('Filter::setCriteria - generic check failed ["'.$_cr[$i].'", "'.$_crs[$i].'", "'.$_crv[$i].'"]', E_USER_NOTICE);
            $this->error = true;
        }

        $this->fiSetCriteria = array(
            'cr'  => $_cr,
            'crs' => $_crs,
            'crv' => $_crv
        );
    }

    public function evalWeights() : void
    {
        // both empty: not in use; not an error
        if (!$this->values['wt'] && !$this->values['wtv'])
            return;

        // one empty: erroneous manual input?
        if (!$this->values['wt'] || !$this->values['wtv'])
        {
            unset($this->values['wt']);
            unset($this->values['wtv']);

            trigger_error('Filter::setWeights - one of wt, wtv is missing', E_USER_NOTICE);
            $this->error = true;
            return;
        }

        $_wt  = &$this->values['wt'];
        $_wtv = &$this->values['wtv'];

        $nwt  = count($_wt);
        $nwtv = count($_wtv);

        if ($nwt != $nwtv)
        {
            trigger_error('Filter::setWeights - wt, wtv are imbalanced', E_USER_NOTICE);
            $this->error = true;
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

    protected function tokenizeString(array $fields, string $string = '', bool $exact = false, bool $shortStr = false) : array
    {
        if (!$string && $this->values['na'])
            $string = $this->values['na'];

        $qry = [];
        foreach ($fields as $f)
        {
            $sub   = [];
            $parts = $exact ? [$string] : array_filter(explode(' ', $string));
            foreach ($parts as $p)
            {
                if ($p[0] == '-' && (mb_strlen($p) > 3 || $shortStr))
                    $sub[] = [$f, $this->transformToken(mb_substr($p, 1), $exact), '!'];
                else if ($p[0] != '-' && (mb_strlen($p) > 2 || $shortStr))
                    $sub[] = [$f, $this->transformToken($p, $exact)];
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

    private function genericCriterion(int $cr, int $crs, string $crv) : ?array
    {
        [$crType, $colOrFn, $param1, $param2] = array_pad(static::$genericFilter[$cr], 4, null);
        $result = null;

        switch ($crType)
        {
            case self::CR_NUMERIC:
                $result = $this->genericNumeric($colOrFn, $crv, $crs, $param1);
                break;
            case self::CR_FLAG:
                $result = $this->genericBooleanFlags($colOrFn, $param1, $crs, $param2);
                break;
            case self::CR_STAFFFLAG:
                if (User::isInGroup(U_GROUP_EMPLOYEE) && $crs > 0)
                    $result = $this->genericBooleanFlags($colOrFn, (1 << ($crs - 1)), true);
                break;
            case self::CR_BOOLEAN:
                $result = $this->genericBoolean($colOrFn, $crs, !empty($param1));
                break;
            case self::CR_STRING:
                $result = $this->genericString($colOrFn, $crv, $param1);
                break;
            case self::CR_ENUM:
                if (!$param2 && isset(static::$enums[$cr][$crs]))
                    $result = $this->genericEnum($colOrFn, static::$enums[$cr][$crs]);
                if ($param2 && in_array($crs, static::$enums[$cr]))
                    $result = $this->genericEnum($colOrFn, $crs);
                else if ($param1 && ($crs == self::ENUM_ANY || $crs == self::ENUM_NONE))
                    $result = $this->genericEnum($colOrFn, $crs);
                break;
            case self::CR_CALLBACK:
                $result = $this->{$colOrFn}($cr, $crs, $crv, $param1, $param2);
                break;
            case self::CR_NYI_PH:                           // do not limit with not implemented filters
                if (is_int($param1))
                    return [$param1];

                // for nonsensical values; compare against 0
                if ($this->int2Op($crs) && Util::checkNumeric($crv))
                {
                    if ($crs == '=')
                        $crs = '==';

                    return eval('return ('.$crv.' '.$crs.' 0);') ? [1] : [0];
                }
                else
                    return [0];
        }

        if ($result && $crType == self::CR_NUMERIC && !empty($param2))
            $this->fiExtraCols[] = $cr;

        return $result;
    }


    /***********************************/
    /*     create conditions from      */
    /* non-generic values and criteria */
    /***********************************/

    protected function createSQLForCriterium(int &$cr, int &$crs, string &$crv) : array
    {
        if (!static::$genericFilter)                        // criteria not in use - no error
            return [];

        if (isset(static::$genericFilter[$cr]))
            if ($genCr = $this->genericCriterion($cr, $crs, $crv))
                return $genCr;

        trigger_error('Filter::createSQLForCriterium - received unhandled criterium: ["'.$cr.'", "'.$crs.'", "'.$crv.'"]', E_USER_NOTICE);
        $this->error = true;

        unset($cr, $crs, $crv);

        return [];
    }

    abstract protected function createSQLForValues() : array;
}

?>
