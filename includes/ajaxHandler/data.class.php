<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');

class AjaxData extends AjaxHandler
{
    protected $_get = array(
        'locale'    => ['filter' => FILTER_CALLBACK,           'options' => 'AjaxHandler::checkLocale'  ],
        't'         => ['filter' => FILTER_CALLBACK,           'options' => 'AjaxHandler::checkTextLine'],
        'catg'      => ['filter' => FILTER_SANITIZE_NUMBER_INT                                          ],
        'skill'     => ['filter' => FILTER_CALLBACK,           'options' => 'AjaxData::checkSkill'      ],
        'class'     => ['filter' => FILTER_SANITIZE_NUMBER_INT                                          ],
        'callback'  => ['filter' => FILTER_CALLBACK,           'options' => 'AjaxData::checkCallback'   ]
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
    protected function handleData() : string
    {
        $result = '';

        // different data can be strung together
        foreach ($this->params as $set)
        {
            // requires valid token to hinder automated access
            if ($set != 'item-scaling' && (!$this->_get['t'] || empty($_SESSION['dataKey']) || $this->_get['t'] != $_SESSION['dataKey']))
            {
                trigger_error('AjaxData::handleData - session data key empty or expired', E_USER_ERROR);
                continue;
            }

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
                    $catg = isset($this->_get['catg']) ? $this->_get['catg'] : 'null';
                    if ($catg == 'null')
                        Util::loadStaticFile('p-'.$set, $result, false);
                    else
                        Util::loadStaticFile('p-'.$set.'-'.$catg, $result, true);

                    $result .= "\n\$WowheadProfiler.loadOnDemand('".$set."', ".$catg.");\n";

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
                case 'quick-excludes':
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
                case 'achievements':
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
                    trigger_error('AjaxData::handleData - invalid file "'.$set.'" in request', E_USER_ERROR);
                    break;
            }
        }

        return $result;
    }

    protected static function checkSkill(string $val) : array
    {
        return array_intersect([171, 164, 333, 202, 182, 773, 755, 165, 186, 393, 197, 185, 129, 356], explode(',', $val));
    }

    protected static function checkCallback(string $val) : bool
    {
        return substr($val, 0, 29) === '$WowheadProfiler.loadOnDemand';
    }

    private function loadProfilerData(string $file, string $catg = 'null') : string
    {
        $result = '';
        if ($this->_get['callback'])
            if (Util::loadStaticFile('p-'.$file, $result, true))
                $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".$catg.");\n";

        return $result;
    }

}

?>
