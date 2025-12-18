<?php

namespace Aowow\Template;

use \Aowow\Util, \Aowow\Cfg, \Aowow\Lang, \Aowow\User;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class PageTemplate
{

    private const GUIDE_RATING_TPL = "$(document).ready(function() { $('#guiderating').append(GetStars(%.10F, %s, %u, %u)); });\n";

    private readonly \Aowow\Locale $locale;

    private array $displayHooks = [];

    private array $dataLoader = [];
    private array $scripts    = [];
    private array $js         = [];
    private array $css        = [];

    // generic data, that's just accessed from the template
    private array $rawData  = [];                           // copied from $context
    private array $pageData = [];                           // processed by display hooks

    // template data that needs further processing .. ! WARNING ! they will not get aut fetched from $context as they are already defined here
    private string $gStaticUrl;
    private string $gHost;
    private string $gServerTime;
    private string $gUser;
    private string $gFavorites;
    private bool   $hasAnalytics  = false;
    private bool   $consentFooter = false;
    private string $dbProfiles    = '';

    private readonly string $user;                          // becomes User object

    /*******************/
    /* basic execution */
    /*                 */
    /* 1) Init         */
    /*******************/

    public function __construct(private string $template, private ?\Aowow\TemplateResponse $context = null)
    {
        $this->locale       = Lang::getLocale();
        $this->gStaticUrl   = Cfg::get('STATIC_URL');
        $this->gHost        = Cfg::get('HOST_URL');
        $this->hasAnalytics = !!Cfg::get('GTAG_MEASUREMENT_ID');
        $this->gServerTime  = sprintf("new Date('%s')", date(Util::$dateFormatInternal));
        $this->user         = User::class;
    }

    public function addDataLoader(string ...$dataFile) : void
    {
        foreach ($dataFile as $df)
            $this->dataLoader[] = $df;
    }

    public function addScript(int $type, string $str, int $flags = 0x0) : bool
    {
        $tpl = match ($type)
        {
            SC_CSS_FILE   => '<link rel="stylesheet" type="text/css" href="%1$s%2$s" />',
            SC_CSS_STRING => '<style type="text/css">%1$s</style>',
            SC_JS_FILE    => '<script type="text/javascript" src="%1$s%2$s"></script>',
            SC_JS_STRING  => '<script type="text/javascript">%1$s</script>',
            default       => ''
        };

        if (!$str)
        {
            trigger_error('PageTemplate::addScript - content empty', E_USER_WARNING);
            return false;
        }

        if (!$tpl)
        {
            trigger_error('PageTemplate::addScript - unknown script type #'.$type, E_USER_WARNING);
            return false;
        }

        // insert locale string
        if ($flags & SC_FLAG_LOCALIZED)
            $str = sprintf($str, Lang::getLocale()->json());

        $this->scripts[] = [$type, $str, $flags, $tpl];
        return true;
    }

    /* (optional) set pre-render hooks */

    public function registerDisplayHook(string $var, callable $fn) : void
    {
        $this->displayHooks[$var][] = $fn;
    }

    private function getDisplayHooks(string $var) : array
    {
        return $this->displayHooks[$var] ?? [];
    }

    /* 3) self test, ready to be cached now */

    public function prepare() : bool
    {
        if (!self::test('template/pages/', $this->template))
        {
            trigger_error('Error: nonexistent template requested: template/pages/'.$this->template.'.tpl.php', E_USER_ERROR);
            return false;
        }

        // TODO - more checks and preparations

        return true;
    }

    /* 4) display */

    public function render() : void
    {
        $this->update();

        include('template/pages/'.$this->template.'.tpl.php');
    }


    /***********/
    /* loaders */
    /***********/

    // "template_exists"
    public static function test(string $path, string $file) : bool
    {
        if (!preg_match('/^[\w\-_]+(\.tpl(\.php)?)?$/i', $file))
            return false;

        if ($path && preg_match('/\\{2,}|\/{2,}|\.{2,}|~/i', $path))
            return false;

        if (!is_file('template/'.$path.$file))
            return false;

        return true;
    }

    // load brick
    private function brick(string $file, array $localVars = []) : void
    {
        $file .= '.tpl.php';

        if (!self::test('bricks/', $file))
        {
            trigger_error('Nonexistent template requested: template/bricks/'.$file, E_USER_ERROR);
            return;
        }

        foreach ($localVars as $n => $v)
            $$n = $v;

        include('template/bricks/'.$file);
    }

    private function brickIf(mixed $boolish, string $file, array $localVars = []) : void
    {
        if ($boolish)
            $this->brick($file, $localVars);
    }

    // load brick with more text then vars
    private function localizedBrick(string $file, array $localVars = []) : void
    {
        foreach ($localVars as $n => $v)
            $$n = $v;

        $_file = $file.'_'.$this->locale->value.'.tpl.php';
        if (self::test('localized/', $_file))
        {
            include('template/localized/'.$_file);
            return;
        }

        $_file = $file.'_'.$this->locale->getFallback()->value.'.tpl.php';
        if (self::test('localized/', $_file))
        {
            include('template/localized/'.$_file);
            return;
        }

        trigger_error('Nonexistent template requested: template/localized/'.$_file, E_USER_ERROR);
    }

    private function localizedBrickIf(mixed $boolish, string $file, array $localVars = []) : void
    {
        if ($boolish)
            $this->localizedBrick($file, $localVars);
    }


    /****************/
    /* Util wrapper */
    /****************/

    private function cfg(string $name) : mixed
    {
        return Cfg::get($name);
    }

    private function json(mixed $var, int $jsonFlags = 0x0) : string
    {
        if (is_string($var) && $this->$var)
            $var = $this->$var;

        return preg_replace('/script\s*\>/i', 'scr"+"ipt>', Util::toJSON($var, $jsonFlags) ?: "{}");
    }

    private function escHTML(string $var) : string|array
    {
        return Util::htmlEscape($this->$var ?? $var);
    }

    private function escJS(string $var) : string|array
    {
        return Util::jsEscape($this->$var ?? $var);
    }

    private function ucFirst(string $var) : string
    {
        return Util::ucFirst($this->$var ?? $var);
    }


    /*****************/
    /* render helper */
    /*****************/

    private function concat(string $arrVar, string $separator = '') : string
    {
        if (!is_array($this->$arrVar))
            return '';

        return implode($separator, $this->$arrVar);
    }

    private function renderArray(string|array $arrVar, int $lpad = 0) : string
    {
        $data = [];
        if (is_string($arrVar) && isset($this->$arrVar) && is_array($this->$arrVar))
            $data = $this->$arrVar;
        else if (is_array($arrVar))
            $data = $arrVar;

        $buff = '';
        foreach ($data as $x)
            $buff .= str_repeat(' ', $lpad) . $x . "\n";

        return $buff;
    }

    // load jsGlobals
    private function renderGlobalVars(int $lpad = 0) : string
    {
        $buff = '';

        if ($this->guideRating)
            $buff .= str_repeat(' ', $lpad).sprintf(self::GUIDE_RATING_TPL, ...$this->guideRating);

        foreach ($this->jsGlobals as [$jsVar, $data, $extraData])
        {
            $buff .= str_repeat(' ', $lpad).'var _ = '.$jsVar.';';

            foreach ($data as $key => $data)
                $buff .= ' _['.(is_numeric($key) ? $key : "'".$key."'")."]=".Util::toJSON($data).';';

            $buff .= "\n";

            if (isset($this->gPageInfo['type']) && isset($this->gPageInfo['typeId']) && isset($extraData[$this->gPageInfo['typeId']]))
            {
                $buff .= "\n";
                foreach ($extraData[$this->gPageInfo['typeId']] as $k => $v)
                    if ($v)
                        $buff .= str_repeat(' ', $lpad).'_['.$this->gPageInfo['typeId'].'].'.$k.' = '.Util::toJSON($v).";\n";
                $buff .= "\n";
            }
        }

        return $buff;
    }

    private function renderSeriesItem(int $idx, array $list, int $lpad = 0) : string
    {
        $result = '<tr><th>'.($idx + 1).'</th><td><div>';

        $end = array_key_last($list);
        foreach ($list as $k => $i)                         // itemItr
        {
            $wrap = match ($i['side'])
            {
                SIDE_ALLIANCE => '<span class="icon-alliance-padded">%s</span>',
                SIDE_HORDE    => '<span class="icon-horde">%s</span>',
                default       => '%s'
            };

            if ($i['typeId'] == $this->typeId)
                $result .= sprintf($wrap, '<b>'.$i['name'].'</b>');
            else
                $result .= sprintf($wrap, '<a href="?'.$i['typeStr'].'='.$i['typeId'].'">'.$i['name'].'</a>');

            if ($end != $k)
                $result .= '<br />';

        }

        return str_repeat(' ', $lpad) . $result .  "</div></td></tr>\n";
    }

    private function renderFilter(int $lpad = 0) : string
    {
        $result = [];

        // it's worth noting, that this only works on non-cached page calls. Luckily Profiler pages are not cached.
        if ($this->context instanceof \Aowow\IProfilerList)
        {
            $result[] = "pr_setRegionRealm(\$WH.ge('fi').firstChild, '".$this->region."', '".$this->realm."');";

            if (!empty($this->filter->values['ra']))
                $result[] = "pr_onChangeRace();";
        }

        if ($this->filter->fiInit)                          // str: filter template (and init html form)
            $result[] = "fi_init('".$this->filter->fiInit."');";
        else if ($this->filter->fiType)                     // str: filter template (set without init)
            $result[] = "var fi_type = '".$this->filter->fiType."'";

        if ($this->filter->fiSetCriteria)                   // arr:criteria, arr:signs, arr:values
            $result[] = 'fi_setCriteria('.mb_substr(Util::toJSON($this->filter->fiSetCriteria), 1, -1).");";

        /*
            nt: don't try to match provided weights on predefined weight sets (preselects preset from opt list and ..?)
            ids: weights are encoded as ids, not by their js name and need conversion before use
            stealth: the ub-selector (items filter) will not visually change (so what..?)
        */
        if ($this->filter->fiSetWeights)                    // arr:weights, bool:nt[0], bool:ids[1], bool:stealth[1]
            $result[] = 'fi_setWeights('.Util::toJSON(array_values($this->filter->fiSetWeights)).', 0, 1, 1);';

        if ($this->filter->fiExtraCols)                     // arr:extraCols
            $result[] = 'fi_extraCols = '.Util::toJSON(array_values(array_unique($this->filter->fiExtraCols))).";";

        return str_repeat(' ', $lpad)."<script type=\"text/javascript\">//<![CDATA[\n".
                   $this->renderArray($result, $lpad + 4).
               str_repeat(' ', $lpad)."//]]></script>\n";
    }

    private function makeOptionsList(array $data, mixed $selectedIdx = null, int $lpad = 0, ?callable $callback = null) : string
    {
        $callback ??= fn(&$v, &$k) => $v;                   // default callback: skip empty descriptors
        $options = '';

        foreach ($data as $idx => $str)
        {
            $extraAttributes = [];
            if (!$callback($str, $idx, $extraAttributes))
                continue;

            if ($idx === '' || !$str)
                continue;

            $options .= str_repeat(' ', max(0, $lpad)).'<option';

            foreach ($extraAttributes as $k => $v)
                $options .= ' '.$k.'="'.$v.'"';

            if (is_array($selectedIdx) && in_array($idx, $selectedIdx))
                $options .= ' selected="selected"';
            else if (!is_null($selectedIdx) && $selectedIdx == $idx)
                $options .= ' selected="selected"';

            $options .= ' value="'.$idx.'">'.$str.'</option>'.($lpad < 0 ? '' : "\n");
        }

        return $options;
    }

    private function makeRadiosList(string $name, array $data, mixed $selectedIdx = null, int $lpad = 0, ?callable $callback = null) : string
    {
        $callback ??= fn(&$v, &$k) => $v;                   // default callback: skip empty descriptors
        $options = '';

        foreach ($data as $idx => [$title, $id])
        {
            $extraAttributes = [];
            if (!$callback($title, $idx, $extraAttributes))
                continue;

            if ($id === '' || !$title)
                continue;

            $options .= str_repeat(' ', max(0, $lpad)).'<input type="radio" name="'.$name.'" value="'.$idx.'" id="'.$name.'-'.$id.'"';

            if (!is_null($selectedIdx) && $selectedIdx == $idx)
                $options .= ' checked="checked"';

            $options .= '/><label for="'.$name.'-'.$id.'"';

            foreach ($extraAttributes as $k => $v)
                $options .= ' '.$k.'="'.$v.'"';

            $options .= '>'.$title.'</label>'.($lpad < 0 ? '' : "\n");
        }

        return $options;
    }

    // unordered stuff

    private function prepareScripts() : void
    {
        $this->js = $this->css = [];

        foreach ($this->scripts as [$type, $str, $flags, $tpl])
        {
            $app = [];

            if (($flags & SC_FLAG_APPEND_LOCALE) && $this->locale != \Aowow\Locale::EN)
                $app[] = 'lang='.$this->locale->domain();

            // append anti-cache timestamp
            if (!($flags & SC_FLAG_NO_TIMESTAMP))
                if ($type == SC_JS_FILE || $type == SC_CSS_FILE)
                    $app[] = filemtime('static/'.$str) ?: 0;

            if ($app)
                $appendix = '?'.implode('&', $app);

            if ($type == SC_JS_FILE || $type == SC_CSS_FILE)
                $str = Cfg::get('STATIC_URL').'/'.$str;

            if ($flags & SC_FLAG_PREFIX)
            {
                if ($type == SC_JS_FILE || $type == SC_JS_STRING)
                    array_unshift($this->js, sprintf($tpl, $str, $appendix ?? ''));
                else
                    array_unshift($this->css, sprintf($tpl, $str, $appendix ?? ''));
            }
            else
            {
                if ($type == SC_JS_FILE || $type == SC_JS_STRING)
                    array_push($this->js, sprintf($tpl, $str, $appendix ?? ''));
                else
                    array_push($this->css, sprintf($tpl, $str, $appendix ?? ''));
            }
        }

        if ($data = array_unique($this->dataLoader))
        {
            $args = array(
                'data'   => implode('.', $data),
                'locale' => $this->locale->value,
                't'      => $_SESSION['dataKey']
            );

            array_push($this->js, '<script type="text/javascript" src="'.Cfg::get('HOST_URL').'/?'.http_build_query($args).'"></script>');
        }
    }

    // refresh vars that shouldn't be cached
    private function update() : void
    {
        // analytics + consent
        if ($this->hasAnalytics && !isset($_COOKIE['consent']))
        {
            $this->addScript(SC_CSS_FILE, 'css/consent.css', SC_FLAG_NOCACHE);
            $this->addScript(SC_JS_FILE,  'js/consent.js', SC_FLAG_NOCACHE);

            $this->consentFooter = true;
            $this->hasAnalytics  = false;
        }
        else if ($this->hasAnalytics && !$_COOKIE['consent'])
            $this->hasAnalytics = false;

        // js + css
        $this->prepareScripts();

        $this->gUser      = Util::toJSON(User::getUserGlobal());
        $this->gFavorites = Util::toJSON(User::getFavorites());

        // db profiling
        if (Cfg::get('DEBUG') >= LOG_LEVEL_INFO && User::isInGroup(U_GROUP_DEV | U_GROUP_ADMIN))
            $this->dbProfiles = \Aowow\DB::getProfiles();
    }

    public function setListviewError() : void
    {
        if (!$this->lvTabs)
            return;

        foreach ($this->lvTabs->iterate() as $lv)
            if ($lv instanceof \Aowow\Listview)
                $lv->setError(true);
    }

    // pre-serialization: if a var is relevant it was stored in $rawData
    public function __sleep() : array
    {
        $this->context  = null;                             // unlink from TemplateResponse
        $this->pageData = [];                               // clear modified data

        if ($this->lvTabs)                                  // do not store lvErrors in cache
            foreach ($this->lvTabs->iterate() as $lv)
                if ($lv instanceof \Aowow\Listview)
                    $lv->setError(false);

        // clear out scripts flagged as non-caching
        $this->scripts = array_filter($this->scripts, fn($x) => !($x[2] & SC_FLAG_NOCACHE));

        $vars = [];
        foreach ($this as $k => $_)
            $vars[] = $k;

        return $vars;
    }

    public function __wakeup() : void
    {
        $this->gStaticUrl   = Cfg::get('STATIC_URL');
        $this->gHost        = Cfg::get('HOST_URL');
        $this->hasAnalytics = !!Cfg::get('GTAG_MEASUREMENT_ID');
        $this->gServerTime  = sprintf("new Date('%s')", date(Util::$dateFormatInternal));
    }

    public function __set(string $var, mixed $value) : void
    {
        $this->pageData[$var] = $value;
    }

    public function __get(string $var) : mixed
    {
        // modified data exists
        if (isset($this->pageData[$var]))
            return $this->pageData[$var];

        if (!isset($this->rawData[$var]))
        {
            if (!$this->context)
                return null;

            if (!isset(get_object_vars($this->context)[$var]))
                return null;

            $this->rawData[$var] = $this->context->$var;
        }

        if ($hooks = $this->getDisplayHooks($var))
        {
            if (is_object($this->rawData[$var]))            // is frontend component
                $this->pageData[$var] = clone $this->rawData[$var];
            else
                $this->pageData[$var] = $this->rawData[$var];

            foreach ($hooks as $fn)
                $fn($this, $this->pageData[$var]);
        }

        return $this->pageData[$var] ?? $this->rawData[$var];
    }
}
