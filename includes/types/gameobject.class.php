<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class GameObjectList extends BaseType
{
    use listviewHelper, spawnHelper;

    public static   $type      = TYPE_OBJECT;

    protected       $queryBase = 'SELECT *, go.entry AS ARRAY_KEY FROM gameobject_template go';
    protected       $queryOpts = array(
                        'go' => [['lg']],
                        'lg' => ['j' => ['locales_gameobject lq ON go.entry = lq.entry', true]],
                        'l'  => ['j' => ['?_lock l ON l.id = IF(go.type = 3, data0, null)', true], 's' => ', l.type1, l.properties1, l.reqSkill1, l.type2, l.properties2, l.reqSkill2']
                    );

    public function __construct($conditions = [], $applyFilter = false)
    {
        parent::__construct($conditions, $applyFilter);

        if ($this->error)
            return;

        // post processing
        // most of this will be obsolete, when gameobjects get their own table
        foreach ($this->iterate() as $_id => &$curTpl)
        {
            switch ($curTpl['type'])
            {
                case OBJECT_CHEST:
                    $curTpl['lootId']    = $curTpl['data1'];
                    $curTpl['lootStack'] = [$curTpl['data4'], $curTpl['data5']];
                    $curTpl['lockId']    = $curTpl['data0'];

                    if (!isset($curTpl['properties1']))
                        break;

                    if ($curTpl['properties1'] == LOCK_PROPERTY_HERBALISM)
                    {
                        $curTpl['reqSkill'] = $curTpl['reqSkill1'];
                        $curTpl['type'] = -3;
                    }
                    else if ($curTpl['properties1'] == LOCK_PROPERTY_MINING)
                    {
                        $curTpl['reqSkill'] = $curTpl['reqSkill1'];
                        $curTpl['type'] = -4;
                    }
                    else if ($curTpl['properties1'] == LOCK_PROPERTY_FOOTLOCKER)
                    {
                        $curTpl['reqSkill'] = $curTpl['reqSkill1'];
                        $curTpl['type'] = -5;
                    }
                    else if( $curTpl['properties2'] == LOCK_PROPERTY_FOOTLOCKER)
                    {
                        $curTpl['reqSkill'] = $curTpl['reqSkill2'];
                        $curTpl['type'] = -5;
                    }

                    break;
                case OBJECT_FISHINGHOLE:
                    $curTpl['lootId']    = $curTpl['data1'];
                    $curTpl['lootStack'] = [$curTpl['data2'], $curTpl['data3']];
                    break;
                default:                                    // adding more, when i need them
                    $curTpl['lockId'] = 0;
                    break;
            }

            for ($i = 0; $i < 24; $i++)                     // kill indescriptive/unused fields
                unset($curTpl['data'.$i]);
        }
    }

    public static function getName($id)
    {
        $n = DB::Aowow()->SelectRow('
            SELECT
                name,
                name_loc2,
                name_loc3,
                name_loc6,
                name_loc8
            FROM
                gameobject_template gt
            LEFT JOIN
                locales_gameobject lg
            ON
                lg.entry = gt.entry
            WHERE
                gt.entry = ?d',
            $id
        );
        return Util::localizedString($n, 'name');
    }

    public function getListviewData()
    {
        $data = [];
        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'id'   => $this->id,
                'name' => $this->getField('name', true),
                'type' => $this->curTpl['type']
            );

            if (!empty($this->curTpl['reqSkill']))
                $data[$this->id]['skill'] = $this->curTpl['reqSkill'];
        }

        return $data;
    }

    public function renderTooltip($interactive = false)
    {
        if (!$this->curTpl)
            return array();

        if (isset($this->tooltips[$this->id]))
            return $this->tooltips[$this->id];

        $x  = '<table>';
        $x .= '<tr><td><b class="q">'.$this->getField('name', true).'</b></td></tr>';
        $x .= '<tr><td>[TYPE '.$this->curTpl['type'].']</td></tr>';
        if ($locks = Lang::getLocks($this->curTpl['lockId']))
            foreach ($locks as $l)
                $x .= '<tr><td>'.$l.'</td></tr>';

        $x .= '</table>';

        $this->tooltips[$this->id] = $x;

        return $this->tooltips[$this->id];
    }

    public function addGlobalsToJScript(&$template, $addMask = 0) { }
}

?>
