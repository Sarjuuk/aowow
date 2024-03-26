<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminPage extends GenericPage
{
    protected $getAll    = null;
    protected $ssPages   = [];
    protected $ssData    = [];
    protected $ssNFound  = 0;
    protected $lvTabs    = [];
    protected $extraText = '';
    protected $extraHTML = '';

    protected $tpl       = null;                            // depends on the subject
    protected $reqUGroup = U_GROUP_NONE;                    // actual group dependant on the subPage
    protected $reqAuth   = true;
    protected $path      = [4];
    protected $tabId     = 4;

    protected $_get      = array(
        'all'    => ['filter' => FILTER_UNSAFE_RAW],
        'type'   => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'typeid' => ['filter' => FILTER_CALLBACK, 'options' => 'GenericPage::checkInt'],
        'user'   => ['filter' => FILTER_CALLBACK, 'options' => 'urldecode']
    );

    private   $generator = '';

    public function __construct($pageCall, $pageParam)
    {
        switch ($pageParam)
        {
            case 'screenshots':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_SCREENSHOT;
                $this->generator = 'handleScreenshots';
                $this->tpl       = 'admin/screenshots';

                array_push($this->path, 1, 5);
                $this->name = 'Screenshot Manager';
                break;
            case 'phpinfo':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_DEV;
                $this->generator = 'handlePhpInfo';
                $this->tpl       = 'list-page-generic';

                array_push($this->path, 2, 21);
                $this->name = 'PHP Information';
                break;
            case 'siteconfig':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_DEV;
                $this->generator = 'handleConfig';
                $this->tpl       = 'admin/siteconfig';

                array_push($this->path, 2, 18);
                $this->name = 'Site Configuration';
                break;
            case 'weight-presets':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_DEV | U_GROUP_BUREAU;
                $this->generator = 'handleWeightPresets';
                $this->tpl       = 'admin/weight-presets';

                array_push($this->path, 2, 16);
                $this->name = 'Weight Presets';
                break;
            case 'guides':
                $this->reqUGroup = U_GROUP_STAFF;
                $this->generator = 'handleGuideApprove';
                $this->tpl       = 'list-page-generic';

                array_push($this->path, 1, 25);
                $this->name = 'Pending Guides';
                break;
            case 'out-of-date':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_MOD;
                $this->generator = 'handleOutOfDate';
                $this->tpl       = 'list-page-generic';

                array_push($this->path, 1, 23);
                $this->name = 'Out of Date Comments';
                break;
            case 'reports':
                $this->reqUGroup = U_GROUP_ADMIN | U_GROUP_BUREAU | U_GROUP_EDITOR | U_GROUP_MOD | U_GROUP_LOCALIZER | U_GROUP_SCREENSHOT | U_GROUP_VIDEO;
                $this->generator = 'handleReports';
                $this->tpl       = 'admin/reports';

                array_push($this->path, 5);
                $this->name = 'Reports';
                break;
            default:                                        // error out through unset template
        }

        parent::__construct($pageCall, $pageParam);
    }

    protected function generateContent() : void
    {
        if (!$this->generator || function_exists($this->generator))
            return;

        $this->{$this->generator}();
    }

    private function handleConfig() : void
    {
        $this->addScript(
            [SC_CSS_STRING, '.grid input[type=\'text\'], .grid input[type=\'number\'] { width:250px; text-align:left; }'],
            [SC_CSS_STRING, '.grid input[type=\'button\'] { width:65px; padding:2px; }'],
            [SC_CSS_STRING, '.grid a.tip { margin:0px 5px; opacity:0.8; }'],
            [SC_CSS_STRING, '.grid a.tip:hover  { opacity:1; }'],
            [SC_CSS_STRING, '.grid tr { height:30px; }'],
            [SC_CSS_STRING, '.grid .disabled { opacity:0.4 !important; }'],
            [SC_CSS_STRING, '.grid .status { position:absolute; right:5px; }']
        );

        $head = '<table class="grid"><tr><th><b>Key</b></th><th><b>Value</b></th><th style="width:150px;"><b>Options</b></th></tr>';
        $mainTab = [];
        $miscTab = [];
        foreach (Util::$configCats as $idx => $catName)
        {
            if ($rows = DB::Aowow()->select('SELECT * FROM ?_config WHERE cat = ?d ORDER BY `flags` DESC, `key` ASC', $idx))
            {
                $buff = $head;
                foreach ($rows as $r)
                    $buff .= $this->configAddRow($r);

                if (!$idx)                                  //cat: misc
                    $buff .= '<tr><td colspan="3"><a class="icon-add" onclick="cfg_add(this)">new configuration</a></td></tr>';

                $buff .= '</table>';

                if ($idx)
                    $mainTab[$catName] = $buff;
                else
                    $miscTab[$catName] = $buff;
            }
        }

        foreach ($mainTab as $n => $t)
            $this->lvTabs[] = [null, array(
                'data' => $t,
                'name' => $n,
                'id'   => Profiler::urlize($n)
            )];

        foreach ($miscTab as $n => $t)
            $this->lvTabs[] = [null, array(
                'data' => $t,
                'name' => $n,
                'id'   => Profiler::urlize($n)
            )];
    }

    private function handlePhpInfo() : void
    {
        $this->addScript([
            SC_CSS_STRING, "\npre {margin: 0px; font-family: monospace;}\n" .
                        "td, th { border: 1px solid #000000; vertical-align: baseline;}\n" .
                        ".p {text-align: left;}\n" .
                        ".e {background-color: #ccccff; font-weight: bold; color: #000000;}\n" .
                        ".h {background-color: #9999cc; font-weight: bold; color: #000000;}\n" .
                        ".v {background-color: #cccccc; color: #000000;}\n" .
                        ".vr {background-color: #cccccc; text-align: right; color: #000000;}\n"
        ]);

        $bits  = [INFO_GENERAL, INFO_CONFIGURATION, INFO_ENVIRONMENT, INFO_MODULES];
        $names = ['General', '', '', 'Module'];
        foreach ($bits as $i => $b)
        {
            ob_start();
            phpinfo($b);
            $buff = ob_get_contents();
            ob_end_clean();

            $buff = explode('<div class="center">', $buff)[1];
            $buff = explode('</div>', $buff);
            array_pop($buff);                               // remove last from stack
            $buff = implode('</div>', $buff);               // sew it together

            if (strpos($buff, '<h1>'))
                $buff = explode('</h1>', $buff)[1];

            if (strpos($buff, '<h2>'))
            {
                $parts = explode('<h2>', $buff);
                foreach ($parts as $p)
                {
                    if (!preg_match('/\w/i', $p))
                        continue;

                    $p = explode('</h2>', $p);

                    $body = substr($p[1], 0, -7);           // remove trailing "<br />\n"
                    $name = $names[$i] ? $names[$i].': ' : '';
                    if (preg_match('/<a[^>]*>([\w\s\d]+)<\/a>/i', $p[0], $m))
                        $name .= $m[1];
                    else
                        $name .= $p[0];

                    $this->lvTabs[] = [null, array(
                        'data' => $body,
                        'id'   => strtolower(strtr($name, [' ' => ''])),
                        'name' => $name
                    )];
                }
            }
            else
            {
                $this->lvTabs[] = [null, array(
                    'data' => $buff,
                    'id'   => strtolower($names[$i]),
                    'name' => $names[$i]
                )];
            }
        }
    }

    private function handleScreenshots() : void
    {
        $this->addScript(
            [SC_JS_FILE,    'js/screenshot.js'],
            [SC_CSS_STRING, '.layout {margin: 0px 25px; max-width: inherit; min-width: 1200px; }'],
            [SC_CSS_STRING, '#highlightedRow { background-color: #322C1C; }']
        );

        $ssGetAll = $this->_get['all'];
        $ssPages  = [];
        $ssData   = [];
        $nMatches = 0;

        if ($this->_get['type'] && $this->_get['typeId'])
        {
            $ssData   = CommunityContent::getScreenshotsForManager($this->_get['type'], $this->_get['typeid']);
            $nMatches = count($ssData);
        }
        else if ($this->_get['user'])
        {
            if (mb_strlen($this->_get['user']) >= 3)
            {
                if ($uId = DB::Aowow()->selectCell('SELECT id FROM ?_account WHERE displayName = ?', ucFirst($this->_get['user'])))
                {
                    $ssData   = CommunityContent::getScreenshotsForManager(0, 0, $uId);
                    $nMatches = count($ssData);
                }
            }
        }
        else
            $ssPages = CommunityContent::getScreenshotPagesForManager($ssGetAll, $nMatches);

        $this->getAll   = $ssGetAll;
        $this->ssPages  = $ssPages;
        $this->ssData   = $ssData;
        $this->ssNFound = $nMatches;                        // ssm_numPagesFound
    }

    private function handleWeightPresets() : void
    {
        $this->addScript(
            [SC_JS_FILE,    'js/filters.js'],
            [SC_CSS_STRING, '.wt-edit {display:inline-block; vertical-align:top; width:350px;}']
        );

        $head = $body = '';

        $scales  = DB::Aowow()->select('SELECT class AS ARRAY_KEY, id AS ARRAY_KEY2, name, icon FROM ?_account_weightscales WHERE userId = 0');
        $weights = DB::Aowow()->selectCol('SELECT awd.id AS ARRAY_KEY, awd.field AS ARRAY_KEY2, awd.val FROM ?_account_weightscale_data awd JOIN ?_account_weightscales ad ON awd.id = ad.id WHERE ad.userId = 0');
        foreach ($scales as $cl => $data)
        {
            $ul = '';
            foreach ($data as $id => $s)
            {
                $weights[$id]['__icon'] = $s['icon'];
                $ul .= '[url=# onclick="loadScale.bind(this, '.$id.')();"]'.$s['name'].'[/url][br]';
            }

            $head .= '[td=header]'.Lang::game('cl', $cl).'[/td]';
            $body .= '[td valign=top]'.$ul.'[/td]';
        }

        $this->extraText = '[table class=grid][tr]'.$head.'[/tr][tr]'.$body.'[/tr][/table]';

        $this->extraHTML = '<script type="text/javascript">var wt_presets = '.Util::toJSON($weights).";</script>\n\n";
    }

    private function handleGuideApprove() : void
    {
        $pending = new GuideList([['status', GUIDE_STATUS_REVIEW]]);
        if ($pending->error)
            $data = [];
        else
        {
            $data   = $pending->getListviewData();
            $latest = DB::Aowow()->selectCol('SELECT `typeId` AS ARRAY_KEY, MAX(`rev`) FROM ?_articles WHERE `type` = ?d AND `typeId` IN (?a) GROUP BY `rev`', Type::GUIDE, $pending->getFoundIDs());
            foreach ($latest as $id => $rev)
                $data[$id]['rev'] = $rev;
        }

        $this->lvTabs[] = [GuideList::$brickFile, array(
            'data'       => array_values($data),
            'hiddenCols' => ['patch', 'comments', 'views', 'rating'],
            'extraCols'  => '$_'
        ), 'guideAdminCol'];
    }

    private function handleOutOfDate() : void
    {
        $data = CommunityContent::getCommentPreviews(['flags' => CC_FLAG_OUTDATED]);

        $this->lvTabs[] = ['commentpreview', array(
            'data'      => $data,
            'extraCols' => '$_'
        ), 'commentAdminCol'];
    }

    private function handleReports() : void
    {
        // todo: handle reports listing
        //
    }

    private function configAddRow($r)
    {
        $buff = '<tr>';
        $info = explode(' - ', $r['comment']);
        $key  = $r['flags'] & CON_FLAG_PHP ? strtolower($r['key']) : strtoupper($r['key']);

        // name
        if (!empty($info[1]))
            $buff .= '<td>'.sprintf(Util::$dfnString, $info[1], $key).'</td>';
        else
            $buff .= '<td>'.$key.'</td>';

        // value
        if ($r['flags'] & CON_FLAG_TYPE_BOOL)
            $buff .= '<td><div id="'.$key.'"><input id="'.$key.'1" type="radio" name="'.$key.'" value="1" '.($r['value'] ? 'checked' : null).' /><label for="'.$key.'1">Enabled</label> <input id="'.$key.'0" type="radio" name="'.$key.'" value="0" '.($r['value'] ? null : 'checked').' /><label for="'.$key.'0">Disabled</label></div></td>';
        else if ($r['flags'] & CON_FLAG_OPT_LIST && !empty($info[2]))
        {
            $buff .= '<td><select id="'.$key.'" name="'.$key.'">';
            foreach (explode(', ', $info[2]) as $option)
            {
                $opt = explode(':', $option);
                $buff .= '<option value="'.$opt[0].'"'.($r['value'] == $opt[0] ? ' selected ' : null).'>'.$opt[1].'</option>';
            }
            $buff .= '</select></td>';
        }
        else if ($r['flags'] & CON_FLAG_BITMASK && !empty($info[2]))
        {
            $buff .= '<td><div id="'.$key.'">';
            foreach (explode(', ', $info[2]) as $option)
            {
                $opt = explode(':', $option);
                $buff .= '<input id="'.$key.$opt[0].'" type="checkbox" name="'.$key.'" value="'.$opt[0].'"'.($r['value'] & (1 << $opt[0]) ? ' checked ' : null).'><label for="'.$key.$opt[0].'">'.$opt[1].'</label>';
            }
            $buff .= '</div></td>';
        }
        else
            $buff .= '<td><input id="'.$key.'" type="'.($r['flags'] & CON_FLAG_TYPE_STRING ? 'text" placeholder="<empty>' : 'number'.($r['flags'] & CON_FLAG_TYPE_FLOAT ? '" step="any' : '')).'" name="'.$key.'" value="'.$r['value'].'" /></td>';

        // actions
        $buff .= '<td style="position:relative;">';

        $buff .= '<a class="icon-save tip" onclick="cfg_submit.bind(this, \''.$key.'\')()" onmouseover="$WH.Tooltip.showAtCursor(event, \'Save Changes\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';

        if (strstr($info[0], 'default:'))
            $buff .= '|<a class="icon-refresh tip" onclick="cfg_default(\''.$key.'\', \''.trim(explode('default:', $info[0])[1]).'\')" onmouseover="$WH.Tooltip.showAtCursor(event, \'Restore Default Value\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';
        else
            $buff .= '|<a class="icon-refresh tip disabled"></a>';

        if (!($r['flags'] & CON_FLAG_PERSISTENT))
            $buff .= '|<a class="icon-delete tip" onclick="cfg_remove.bind(this, \''.$key.'\')()" onmouseover="$WH.Tooltip.showAtCursor(event, \'Remove Setting\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';

        $buff .= '<span class="status"></span></td></tr>';

        return $buff;
    }

    protected function generateTitle() {}
    protected function generatePath() {}
}

?>
