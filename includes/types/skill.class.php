<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillList extends BaseType
{
    public static   $type      = Type::SKILL;
    public static   $brickFile = 'skill';
    public static   $dataTable = '?_skillline';

    protected       $queryBase = 'SELECT sl.*, sl.id AS ARRAY_KEY FROM ?_skillline sl';
    protected       $queryOpts = array(
                        'sl' => [['ic']],
                        'ic' => ['j' => ['?_icons ic ON ic.id = sl.iconId', true], 's' => ', ic.name AS iconString'],
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_ = &$_curTpl['specializations'];              // shorthand
            if (!$_)
                $_ = [0, 0, 0, 0, 0];
            else
            {
                $_ = explode(' ', $_);
                while (count($_) < 5)
                    $_[] = 0;
            }

            if (!$_curTpl['iconId'])
                $_curTpl['iconString'] = DEFAULT_ICON;
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('SELECT name_loc0, name_loc2, name_loc3, name_loc4, name_loc6, name_loc8 FROM ?_skillline WHERE id = ?d', $id);
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'category'        => $this->curTpl['typeCat'],
                'categorybak'     => $this->curTpl['categoryId'],
                'id'              => $this->id,
                'name'            => $this->getField('name', true),
                'profession'      => $this->curTpl['professionMask'],
                'recipeSubclass'  => $this->curTpl['recipeSubClass'],
                'specializations' => Util::toJSON($this->curTpl['specializations'], JSON_NUMERIC_CHECK),
                'icon'            => $this->curTpl['iconString']
            );
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[self::$type][$this->id] = ['name' => $this->getField('name', true), 'icon' => $this->curTpl['iconString']];

        return $data;
    }

    public function renderTooltip() { }
}

?>
