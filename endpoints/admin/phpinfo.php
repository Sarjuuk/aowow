<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class AdminPhpinfoResponse extends TemplateResponse
{
    protected  int    $requiredUserGroup = U_GROUP_ADMIN | U_GROUP_DEV;

    protected  string $template          = 'list-page-generic';
    protected  string $pageName          = 'phpinfo';
    protected ?int    $activeTab         = parent::TAB_STAFF;
    protected  array  $breadcrumb        = [4, 2, 21];      // Staff > Development > PHP Information

    protected function generate() : void
    {
        $this->h1 = 'PHP Information';
        array_unshift($this->title, $this->h1);

        $this->lvTabs = new Tabs(['parent' => "\$\$WH.ge('tabs-generic')"]);

        parent::generate();

        $this->addScript([SC_CSS_STRING, <<<CSS

            pre    { margin: 0px; font-family: monospace; }
            .d, th { border: 1px solid #000000; vertical-align: baseline; }
            .p     { text-align: left; }
            .e     { background-color: #ccccff; font-weight: bold; color: #000000; }
            .h     { background-color: #9999cc; font-weight: bold; color: #000000; }
            .v     { background-color: #cccccc;                    color: #000000; }
            .vr    { background-color: #cccccc; text-align: right; color: #000000; }

    CSS]);

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
                    $name = $names[$i] ? $names[$i].': ' : '';
                    if (preg_match('/<a[^>]*>([\w\s\d]+)<\/a>/i', $p[0], $m))
                        $name .= $m[1];
                    else
                        $name .= $p[0];

                    $this->lvTabs->addDataTab(strtolower(strtr($name, [' ' => ''])), $name, $p[1]);
                }
            }
            else
                $this->lvTabs->addDataTab(strtolower($names[$i]), $names[$i], $buff);
        }
    }
}

?>
