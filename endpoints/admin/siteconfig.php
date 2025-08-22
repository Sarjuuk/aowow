<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminSiteconfigResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_DEV;

    protected  string $template          = 'admin/siteconfig';
    protected  string $pageName          = 'siteconfig';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 2, 18];      // Staff > Development > Site Configuration

    protected function generate() : void
    {
        $this->h1 = 'Site Configuration';
        array_unshift($this->title, $this->h1);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        parent::generate();

        $this->addScript([SC_CSS_STRING, <<<CSS

            .grid input[type='text'], .grid input[type='number'] { width:250px; text-align:left; }
            .grid input[type='button'] { width:65px; padding:2px; }
            .grid a.tip { margin:0px 5px; opacity:0.8; }
            .grid a.tip:hover  { opacity:1; }
            .grid tr { height:30px; }
            .grid .disabled { opacity:0.4 !important; }
            .grid .status { position:absolute; right:5px; }

    CSS]);

        $head = '<tr><th><b>Key</b></th><th><b>Value</b></th><th style="width:150px;"><b>Options</b></th></tr>';
        foreach (Cfg::$categories as $idx => $catName)
        {
            $rows = '';
            foreach (Cfg::forCategory($idx) as $key => [$value, $flags, , $default, $comment])
                $rows .= $this->buildRow($key, $value, $flags, $default, $comment);

            if ($idx == Cfg::CAT_MISCELLANEOUS)
                $rows .= '<tr><td colspan="3"><a class="icon-add" onclick="cfg_add(this)">new configuration</a></td></tr>';

            if (!$rows)
                continue;

            $this->lvTabs->addDataTab(Profiler::urlize($catName), $catName, '<table class="grid">' . $head . $rows . '</table>');
        }
    }

    private function buildRow(string $key, string $value, int $flags, ?string $default, string $comment) : string
    {
        $buff = '<tr>';
        $info = explode(' - ', $comment);
        $key  = $flags & Cfg::FLAG_PHP ? strtolower($key) : strtoupper($key);

        // name
        if (!empty($info[0]))
            $buff .= '<td>'.sprintf(Util::$dfnString, $info[0], $key).'</td>';
        else
            $buff .= '<td>'.$key.'</td>';

        // value
        if ($flags & Cfg::FLAG_TYPE_BOOL)
            $buff .= '<td><div id="'.$key.'"><input id="'.$key.'1" type="radio" name="'.$key.'" value="1" '.($value ? 'checked' : null).' /><label for="'.$key.'1">Enabled</label> <input id="'.$key.'0" type="radio" name="'.$key.'" value="0" '.($value ? null : 'checked').' /><label for="'.$key.'0">Disabled</label></div></td>';
        else if ($flags & Cfg::FLAG_OPT_LIST && !empty($info[1]))
        {
            $buff .= '<td><select id="'.$key.'" name="'.$key.'">';
            foreach (explode(', ', $info[1]) as $option)
            {
                [$idx, $name] = explode(':', $option);
                $buff .= '<option value="'.$idx.'"'.($value == $idx ? ' selected ' : null).'>'.$name.'</option>';
            }
            $buff .= '</select></td>';
        }
        else if ($flags & Cfg::FLAG_BITMASK && !empty($info[1]))
        {
            $buff .= '<td><div id="'.$key.'">';
            foreach (explode(', ', $info[1]) as $option)
            {
                [$idx, $name] = explode(':', $option);
                $buff .= '<input id="'.$key.$idx.'" type="checkbox" name="'.$key.'" value="'.$idx.'"'.($value & (1 << $idx) ? ' checked ' : null).'><label for="'.$key.$idx.'">'.$name.'</label>';
            }
            $buff .= '</div></td>';
        }
        else
            $buff .= '<td><input id="'.$key.'" type="'.($flags & Cfg::FLAG_TYPE_STRING ? 'text" placeholder="<empty>' : 'number'.($flags & Cfg::FLAG_TYPE_FLOAT ? '" step="any' : '')).'" name="'.$key.'" value="'.$value.'" /></td>';

        // actions
        $buff .= '<td style="position:relative;">';

        $buff .= '<a class="icon-save tip" onclick="cfg_submit.bind(this, \''.$key.'\')()" onmouseover="$WH.Tooltip.showAtCursor(event, \'Save Changes\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';

        if ($default)
            $buff .= '|<a class="icon-refresh tip" onclick="cfg_default(\''.$key.'\', \''.$default.'\')" onmouseover="$WH.Tooltip.showAtCursor(event, \'Restore Default Value\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';
        else
            $buff .= '|<a class="icon-refresh tip disabled"></a>';

        if (!($flags & Cfg::FLAG_PERSISTENT))
            $buff .= '|<a class="icon-delete tip" onclick="cfg_remove.bind(this, \''.$key.'\')()" onmouseover="$WH.Tooltip.showAtCursor(event, \'Remove Setting\', 0, 0, \'q\')" onmousemove="$WH.Tooltip.cursorUpdate(event)" onmouseout="$WH.Tooltip.hide()"></a>';

        $buff .= '<span class="status"></span></td></tr>';

        return $buff;
    }
}

?>
