<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class SkillList extends BaseType
{
    public static $type = TYPE_SKILL;

    protected     $setupQuery = 'SELECT *, id AS ARRAY_KEY FROM ?_skillLine sl WHERE [cond] ORDER BY id ASC';
    protected     $matchQuery = 'SELECT COUNT(1) FROM ?_skillLine sl WHERE [cond]';

    public function __construct($data)
    {
        parent::__construct($data);

        // post processing
        while ($this->iterate())
        {
            $_ = &$this->curTpl['specializations'];         // shorthand
            if (!$_)
                $_ = [0, 0, 0, 0, 0];
            else
            {
                $_ = explode(' ', $_);
                while (count($_) < 5)
                    $_[] = 0;
            }

            $this->templates[$this->id] = $this->curTpl;
        }

        $this->reset();                                     // push first element back for instant use
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name_loc0,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                ?_skillLine
            WHERE
                id = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];

        while ($this->iterate())
        {
            $data[$this->id] = array(
                'category'        => $this->curTpl['typeCat'],
                'categorybak'     => $this->curTpl['categoryId'],
                'id'              => $this->id,
                'name'            => Util::jsEscape($this->getField('name', true)),
                'profession'      => $this->curTpl['professionMask'],
                'recipeSubclass'  => $this->curTpl['recipeSubClass'],
                'specializations' => json_encode($this->curTpl['specializations'], JSON_NUMERIC_CHECK),
                'icon'            => Util::jsEscape($this->curTpl['iconString'])
            );
        }

        return $data;
    }

    public function addGlobalsToJScript(&$template, $addMask = 0)
    {
        while ($this->iterate())
        {
            $template->extendGlobalData(self::$type, [
                $this->id => [
                    'name' => Util::jsEscape($this->getField('name', true)),
                    'icon' => Util::jsEscape($this->curTpl['iconString'])
                ]
            ]);
        }
    }

    public function renderTooltip() { }
}

?>
