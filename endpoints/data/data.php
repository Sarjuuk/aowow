<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class DataBaseResponse extends TextResponse
{
    protected array $expectedGET = array(
        'locale'   => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkLocale'         ]],
        't'        => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkTextLine'       ]],
        'catg'     => ['filter' => FILTER_VALIDATE_INT                                                    ],
        'skill'    => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkSkill'          ]],
        'class'    => ['filter' => FILTER_VALIDATE_INT, 'options' => ['min_range' => 1, 'max_range' => 11]],
        'callback' => ['filter' => FILTER_CALLBACK,     'options' => [self::class, 'checkCallback'       ]]
    );

    public function __construct(string $rawParam)
    {
        parent::__construct($rawParam);

        if ($this->_get['locale']?->validate())
            Lang::load($this->_get['locale']);
    }

    protected function generate() : void
    {
        // different data can be strung together
        foreach ($this->params as $set)
        {
            // requires valid token to hinder automated access
            if ($set != 'item-scaling' && (!$this->_get['t'] || empty($_SESSION['dataKey']) || $this->_get['t'] != $_SESSION['dataKey']))
            {
                trigger_error('DataBaseResponse::generate - session data key empty or expired', E_USER_ERROR);
                continue;
            }

            /*  issue on no initial data:
                when we loadOnDemand, the jScript tries to generate the catg-tree before it is initialized
                it cant be initialized, without loading the data as empty catg are omitted
                loading the data triggers the generation of the catg-tree
            */

            $this->result .= match($set)
            {
                'factions'   => $this->loadProfilerData($set),
                'mounts'     => $this->loadProfilerData($set, SKILL_MOUNTS),
                'companions' => $this->loadProfilerData($set, SKILL_COMPANIONS),
                'quests'     => $this->loadProfilerQuests($set, $this->_get['catg']),
                'recipes'    => $this->loadProfilerRecipes(),
                // locale independent
                'quick-excludes',
                'weight-presets',
                'item-scaling',
                'realms',
                'statistics' => $this->loadAgnosticFile($set),
                // localized
                'talents',
                'achievements',
                'pet-talents',
                'glyphs',
                'gems',
                'enchants',
                'itemsets',
                'pets',
                'zones' => $this->loadLocalizedFile($set),
                default => (function($x) { trigger_error('DataBaseResponse::generate - invalid file "'.$x.'" in request', E_USER_ERROR); })($set),
            };
        }
    }

    private function loadProfilerRecipes() : string
    {
        if (!$this->_get['callback'] || !$this->_get['skill'])
            return '';

        $result = '';

        foreach ($this->_get['skill'] as $s)
            Util::loadStaticFile('p-recipes-'.$s, $result, true);

        Util::loadStaticFile('p-recipes-sec', $result, true);
        $result .= "\n\$WowheadProfiler.loadOnDemand('recipes', null);\n";

        return $result;
    }

    private function loadProfilerQuests(string $file, ?string $catg = null) : string
    {
        $result = '';

        if ($catg === null)
            Util::loadStaticFile('p-'.$file, $result, false);
        else
            Util::loadStaticFile('p-'.$file.'-'.$catg, $result, true);

        $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".($catg ?? 'null').");\n";

        return $result;
    }

    private function loadProfilerData(string $file, ?string $catg = null) : string
    {
        $result = '';

        if ($this->_get['callback'])
            if (Util::loadStaticFile('p-'.$file, $result, true))
                $result .= "\n\$WowheadProfiler.loadOnDemand('".$file."', ".($catg ?? 'null').");\n";

        return $result;
    }

    private function loadAgnosticFile(string $file) : string
    {
        $result = '';

        if (!Util::loadStaticFile($file, $result) && Cfg::get('DEBUG'))
            $result .= "alert('could not fetch static data: ".$file."');";

        return $result . "\n\n";
    }

    private function loadLocalizedFile(string $file) : string
    {
        $result = '';

        if ($file == 'talents' && ($_ = $this->_get['class']))
            $file .= "-".$_;

        if (!Util::loadStaticFile($file, $result, true) && Cfg::get('DEBUG'))
            $result .= "alert('could not fetch static data: ".$file." for locale: ".Lang::getLocale()->json()."');";

        return $result . "\n\n";
    }

    protected static function checkSkill(string $val) : array
    {
        return array_intersect(array_merge(SKILLS_TRADE_PRIMARY, [SKILL_FIRST_AID, SKILL_COOKING, SKILL_FISHING]), explode(',', $val));
    }

    protected static function checkCallback(string $val) : bool
    {
        return substr($val, 0, 29) === '$WowheadProfiler.loadOnDemand';
    }
}

?>
