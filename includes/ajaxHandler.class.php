<?php

if (!defined('AOWOW_REVISION'))
    die('invalid access');


class AjaxHandler
{
    private $params;
    private $get;
    private $post;

    public function __construct($params)
    {
        $this->params = explode('.', $params);

        foreach ($_POST as $k => $v)
        {
            Util::checkNumeric($v);
            $this->post[$k] = $v;
        }

        foreach ($_GET as $k => $v)
            $this->get[$k] = Util::checkNumeric($v) ? $v : is_string($v) ? trim(urldecode($v)) : $v;
    }

    public function handle($what)
    {
        $f = 'handle'.ucFirst($what);
        if (!method_exists($this, $f))
            return null;

        return $this->$f();
    }

    private function isLoadOnDemand()
    {
        return substr(@$this->get['callback'], 0, 29) == '$WowheadProfiler.loadOnDemand';
    }

    private function loadProfilerData($file, $catg = 'null')
    {
        $result = '';
        if ($this->isLoadOnDemand())
            if (Util::loadStaticFile('p-'.$file, $result, true))
                $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".$catg.");\n";

        return $result;
    }

    /* responses
        <string>
    */
    private function handleData()
    {
        if (isset($this->get['locale']) && is_numeric($this->get['locale']))
            User::useLocale($this->get['locale']);

        $result = '';

        // different data can be strung together
        foreach ($this->params as $set)
        {
            // requires valid token to hinder automated access
            if ($set != 'item-scaling')
                if (empty($this->get['t']) || empty($_SESSION['dataKey']) || $this->get['t'] != $_SESSION['dataKey'])
                    continue;

            switch ($set)
            {
                /*  issue on no initial data:
                    when we loadOnDemand, the jScript tries to generate the catg-tree before it is initialized
                    it cant be initialized, without loading the data as empty catg are omitted
                    loading the data triggers the generation of the catg-tree
                */
                case 'factions':
                    $result .= $this->loadProfilerData($set);
                    break;
                case 'companions':
                    $result .= $this->loadProfilerData($set, '778');
                    break;
                case 'mounts':
                    $result .= $this->loadProfilerData($set, '777');
                    break;
                case 'quests':
                    // &partial: im not doing this right
                    // it expects a full quest dump on first lookup but will query subCats again if clicked..?
                    // for now omiting the detail clicks with empty results and just set catg update
                    $catg = isset($this->get['catg']) ? $this->get['catg'] : 'null';
                    if ($catg == 'null')
                        $result .= $this->loadProfilerData($set);
                    else if ($this->isLoadOnDemand())
                        $result .= "\n\$WowheadProfiler.loadOnDemand('quests', ".$catg.");\n";

                    break;
                case 'recipes':
                    if (!$this->isLoadOnDemand() || empty($this->get['skill']))
                        break;

                    $skills = array_intersect(explode(',', $this->get['skill']), [171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356]);
                    if (!$skills)
                        break;

                    foreach ($skills as $s)
                        Util::loadStaticFile('p-recipes-'.$s, $result, true);

                    Util::loadStaticFile('p-recipes-sec', $result, true);
                    $result .= "\n\$WowheadProfiler.loadOnDemand('recipes', null);\n";

                    break;
                // locale independant
                case 'quick-excludes':                              // generated per character in profiler
                case 'zones':
                case 'weight-presets':
                case 'item-scaling':
                case 'realms':
                case 'statistics':
                    if (!Util::loadStaticFile($set, $result) && CFG_DEBUG)
                        $result .= "alert('could not fetch static data: ".$set."');";

                    $result .= "\n\n";
                    break;
                // localized
                case 'talents':
                    if (isset($this->get['class']))
                        $set .= "-".intVal($this->get['class']);
                case 'pet-talents':
                case 'glyphs':
                case 'gems':
                case 'enchants':
                case 'itemsets':
                case 'pets':
                    if (!Util::loadStaticFile($set, $result, true) && CFG_DEBUG)
                        $result .= "alert('could not fetch static data: ".$set." for locale: ".User::$localeString."');";

                    $result .= "\n\n";
                    break;
                default:
                    break;
            }
        }
        
        return $result;
    }

    /* responses
        0: success
        $: silent error
    */
    private function handleCookie()
    {
        if (User::$id && $this->params && !empty($this->get[$this->params[0]]))
            if (DB::Aowow()->query('REPLACE INTO ?_account_cookies VALUES (?d, ?, ?)', User::$id, $this->params[0], $this->get[$this->params[0]]))
                return 0;

        return null;
    }

    /* responses
        0: success
        1: captcha invalid
        2: description too long
        3: reason missing
        7: already reported
        $: prints response
    */
    private function handleContactus()
    {
        $mode = @$this->post['mode'];
        $rsn  = @$this->post['reason'];
        $ua   = @$this->post['ua'];
        $app  = @$this->post['appname'];
        $url  = @$this->post['page'];
        $desc = @$this->post['desc'];

        $subj = @intVal($this->post['id']);

        $contexts = array(
            [1, 2, 3, 4, 5, 6, 7, 8],
            [15, 16, 17, 18, 19, 20],
            [30, 31, 32, 33, 34, 35, 36, 37],
            [45, 46, 47, 48],
            [60, 61],
            [45, 46, 47, 48],
            [45, 46, 48]
        );

        if ($mode === null || $rsn === null || $ua === null || $app === null || $url === null)
            return 'required field missing';

        if (!isset($contexts[$mode]) || !in_array($rsn, $contexts[$mode]))
            return 'mode invalid';

        if (!$desc)
            return 3;

        if (strlen($desc) > 500)
            return 2;

        // check already reported
        $field = User::$id ? 'userId' : 'ip';
        if (DB::Aowow()->selectCell('SELECT 1 FROM ?_reports WHERE `mode` = ?d AND `reason`= ?d AND `subject` = ?d AND ?# = ?', $mode, $rsn, $subj, $field, User::$id ? User::$id : $_SERVER['REMOTE_ADDR']))
            return 7;

        $update = array(
            'userId'      => User::$id,
            'mode'        => $mode,
            'reason'      => $rsn,
            'ip'          => $_SERVER['REMOTE_ADDR'],
            'description' => $desc,
            'userAgent'   => $ua,
            'appName'     => $app,
            'url'         => $url
        );

        if ($subj)
            $update['subject'] = $subj;

        if ($_ = @$this->post['relatedurl'])
            $update['relatedurl'] = $_;

        if ($_ = @$this->post['email'])
            $update['email'] = $_;

        if (DB::Aowow()->query('INSERT INTO ?_reports (?#) VALUES (?a)', array_keys($update), array_values($update)))
            return 0;

        return 'save to db unsuccessful';
    }

    /* responses
        - rate:
            0: success
            1: ratingban
            3: rated too often
            $: silent error
        - rating:
            yet to check
    */
    private function handleComment()
    {
        switch ($this->param[0])
        {
            case 'rating':
                return '{"success":true,"error":"","up":7,"down":9}';
            case 'rate':
                return 3;
            default:
                return null;
        }
    }

    private function handleLocale()                         // not sure if this should be here..
    {
        User::setLocale($this->param[0]);
        User::writeCookie();
        header('Location: '.(isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '.'));
    }
}

?>
