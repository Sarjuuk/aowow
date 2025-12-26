<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillList extends DBTypeList
{
    public static int    $type      = Type::SKILL;
    public static string $brickFile = 'skill';
    public static string $dataTable = '?_skillline';

    protected string $queryBase = 'SELECT sl.*, sl.`id` AS ARRAY_KEY FROM ?_skillline sl';
    protected array  $queryOpts = array(
                        'sl' => [['ic']],
                        'ic' => ['j' => ['?_icons ic ON ic.`id` = sl.`iconId`', true], 's' => ', ic.`name` AS "iconString"'],
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

        // post processing
        foreach ($this->iterate() as &$_curTpl)
        {
            $_ = &$_curTpl['specializations'];              // shorthand
            if (!$_)
                $_ = [0, 0, 0, 0, 0];
            else
                $_ = array_pad(explode(' ', $_), 5, 0);

            if (!$_curTpl['iconId'])
                $_curTpl['iconString'] = DEFAULT_ICON;
        }
    }

    public function getListviewData() : array
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

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[self::$type][$this->id] = ['name' => $this->getField('name', true), 'icon' => $this->curTpl['iconString']];

        return $data;
    }

    public function renderTooltip() : ?string { return null; }
}

?>
