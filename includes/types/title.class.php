<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class TitleList extends BaseType
{
    use listviewHelper;

    public static   $type      = TYPE_TITLE;
    public static   $brickFile = 'title';
    public static   $dataTable = '?_titles';

    public          $sources   = [];

    protected       $queryBase = 'SELECT t.*, id AS ARRAY_KEY FROM ?_titles t';
    protected       $queryOpts = array(
                        't'   => [['src']],                 //    11: TYPE_TITLE
                        'src' => ['j' => ['?_source src ON type = 11 AND typeId = t.id', true], 's' => ', src13, moreType, moreTypeId']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as $id => &$_curTpl)
        {
            // preparse sources - notice: under this system titles can't have more than one source (or two for achivements), which is enough for standard TC cases but may break custom cases
            if ($_curTpl['moreType'] == TYPE_ACHIEVEMENT)
                $this->sources[$this->id][12][] = $_curTpl['moreTypeId'];
            else if ($_curTpl['moreType'] == TYPE_QUEST)
                $this->sources[$this->id][4][] = $_curTpl['moreTypeId'];
            else if ($_curTpl['src13'])
                $this->sources[$this->id][13][] = $_curTpl['src13'];

            // titles display up to two achievements at once
            if ($_curTpl['src12Ext'])
                $this->sources[$this->id][12][] = $_curTpl['src12Ext'];

            unset($_curTpl['src12Ext']);
            unset($_curTpl['moreType']);
            unset($_curTpl['moreTypeId']);
            unset($_curTpl['src3']);

            // shorthand for more generic access
            foreach (Util::$localeStrings as $i => $str)
                if ($str)
                    $_curTpl['name_loc'.$i] = trim(str_replace('%s', '', $_curTpl['male_loc'.$i]));
        }
    }

    public function getListviewData()
    {
        $data = [];
        $this->createSource();

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'         => $this->id,
                'name'       => $this->getField('male', true),
                'namefemale' => $this->getField('female', true),
                'side'       => $this->curTpl['side'],
                'gender'     => $this->curTpl['gender'],
                'expansion'  => $this->curTpl['expansion'],
                'category'   => $this->curTpl['category']
            );

            if (!empty($this->curTpl['source']))
                $data[$this->id]['source'] = $this->curTpl['source'];
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[TYPE_TITLE][$this->id]['name'] = $this->getField('male', true);

            if ($_ = $this->getField('female', true))
                $data[TYPE_TITLE][$this->id]['namefemale'] = $_;
        }

        return $data;
    }

    private function createSource()
    {
        $sources = array(
            4  => [],                                       // Quest
            12 => [],                                       // Achievements
            13 => []                                        // simple text
        );

        foreach ($this->iterate() as $__)
        {
            if (empty($this->sources[$this->id]))
                continue;

            foreach (array_keys($sources) as $srcKey)
                if (isset($this->sources[$this->id][$srcKey]))
                    $sources[$srcKey] = array_merge($sources[$srcKey], $this->sources[$this->id][$srcKey]);
        }

        // fill in the details
        if (!empty($sources[4]))
            $sources[4] = (new QuestList(array(['id', $sources[4]])))->getSourceData();

        if (!empty($sources[12]))
            $sources[12] = (new AchievementList(array(['id', $sources[12]])))->getSourceData();

        if (!empty($sources[13]))
            $sources[13] = DB::Aowow()->SELECT('SELECT *, Id AS ARRAY_KEY FROM ?_sourcestrings WHERE Id IN (?a)', $sources[13]);

        foreach ($this->sources as $Id => $src)
        {
            $tmp = [];

            // Quest-source
            if (isset($src[4]))
            {
                foreach ($src[4] as $s)
                {
                    if (isset($sources[4][$s]['s']))
                        $this->faction2Side($sources[4][$s]['s']);

                    $tmp[4][] = $sources[4][$s];
                }
            }

            // Achievement-source
            if (isset($src[12]))
            {
                foreach ($src[12] as $s)
                {
                    if (isset($sources[12][$s]['s']))
                        $this->faction2Side($sources[12][$s]['s']);

                    $tmp[12][] = $sources[12][$s];
                }
            }

            // other source (only one item possible, so no iteration needed)
            if (isset($src[13]))
                $tmp[13] = [Util::localizedString($sources[13][$this->sources[$Id][13][0]], 'source')];

            $this->templates[$Id]['source'] = $tmp;
        }
    }

    public function getHtmlizedName($gender = GENDER_MALE)
    {
        $field = $gender == GENDER_FEMALE ? 'female' : 'male';
        return str_replace('%s', '<span class="q0">&lt;'.Util::ucFirst(Lang::main('name')).'&gt;</span>', $this->getField($field, true));
    }

    public function renderTooltip() { }

    private function faction2Side(&$faction)                // thats weird.. and hopefully unique to titles
    {
        if ($faction == 2)                                  // Horde
            $faction = 0;
        else if ($faction != 1)                             // Alliance
            $faction = -1;                                  // Both
    }
}

?>
