<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxData extends AjaxHandler
{
    protected $_get = array(
        'locale'    => [FILTER_CALLBACK,            ['options' => 'AjaxHandler::checkLocale']],
        't'         => [FILTER_SANITIZE_STRING,     0xC],   // FILTER_FLAG_STRIP_LOW | *_HIGH
        'catg'      => [FILTER_SANITIZE_NUMBER_INT, null],
        'skill'     => [FILTER_CALLBACK,            ['options' => 'AjaxData::checkSkill']],
        'class'     => [FILTER_SANITIZE_NUMBER_INT, null],
        'callback'  => [FILTER_CALLBACK,            ['options' => 'AjaxData::checkCallback']]
    );

    public function __construct(array $params)
    {
        parent::__construct($params);

        if (is_numeric($this->_get['locale']))
            User::useLocale($this->_get['locale']);

        // always this one
        $this->handler = 'handleData';
    }

    /* responses
        <string>
    */
    protected function handleData()
    {
        $result = '';

        // different data can be strung together
        foreach ($this->params as $set)
        {
            // requires valid token to hinder automated access
            if ($set != 'item-scaling')
                if (!$this->_get['t'] || empty($_SESSION['dataKey']) || $this->_get['t'] != $_SESSION['dataKey'])
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
                    $catg = $this->_get['catg'] ?: 'null';
                    if ($catg == 'null')
                        $result .= $this->loadProfilerData($set);
                    else if ($this->_get['callback'])
                        $result .= "\n\$WowheadProfiler.loadOnDemand('quests', ".$catg.");\n";

                    break;
                case 'recipes':
                    if (!$this->_get['callback'] || !$this->_get['skill'])
                        break;

                    foreach ($this->_get['skill'] as $s)
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
                    if ($_ = $this->_get['class'])
                        $set .= "-".$_;
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

    private function checkSkill($val)
    {
        return array_intersect([171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356], explode(',', $val));
    }

    private function checkCallback($val)
    {
        return substr($val, 0, 29) == '$WowheadProfiler.loadOnDemand';
    }

    private function loadProfilerData($file, $catg = 'null')
    {
        $result = '';
        if ($this->_get['callback'])
            if (Util::loadStaticFile('p-'.$file, $result, true))
                $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".$catg.");\n";

        return $result;
    }

}

?>