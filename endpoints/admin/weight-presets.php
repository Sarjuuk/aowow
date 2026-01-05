<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminWeightpresetsResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_DEV | U_GROUP_BUREAU;

    protected  string $template          = 'admin/weight-presets';
    protected  string $pageName          = 'weight-presets';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 2, 16];      // Staff > Development > Weight Presets

    protected  array  $scripts           = array(
        [SC_JS_FILE,    'js/filters.js'],
        [SC_CSS_STRING, '.wt-edit {display:inline-block; vertical-align:top; width:350px;}']
    );

    protected function generate() : void
    {
        $this->h1 = 'Weight Presets';
        array_unshift($this->title, $this->h1);

        $head = $body = '';

        $scales  = DB::Aowow()->selectAssoc('SELECT `class` AS ARRAY_KEY, `id` AS ARRAY_KEY2, `name`, `icon` FROM ::account_weightscales WHERE `userId` = 0');
        $weights = DB::Aowow()->selectCol('SELECT awd.`id` AS ARRAY_KEY, awd.`field` AS ARRAY_KEY2, awd.`val` FROM ::account_weightscale_data awd JOIN ::account_weightscales ad ON awd.`id` = ad.`id` WHERE ad.`userId` = 0');
        foreach ($scales as $cl => $data)
        {
            $ul = '';
            foreach ($data as $id => $s)
            {
                $weights[$id]['__icon'] = $s['icon'];
                $ul .= '[url=# onclick="loadScale.bind(this, '.$id.')();"]'.$s['name'].'[/url][br]';
            }

            $head .= '[td=header][class='.$cl.'][/td]';
            $body .= '[td valign=top]'.$ul.'[/td]';
            $this->extendGlobalIds(Type::CHR_CLASS, $cl);
        }

        $this->extraText = new Markup('[table class=grid][tr]'.$head.'[/tr][tr]'.$body.'[/tr][/table]', ['allow' => Markup::CLASS_ADMIN], 'text-generic');

        $this->extraHTML = '<script type="text/javascript">var wt_presets = '.Util::toJSON($weights).";</script>\n\n";

        parent::generate();
    }
}

?>
