<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldEventList extends BaseType
{
    public static   $type      = TYPE_WORLDEVENT;
    public static   $brickFile = 'event';
    public static   $dataTable = '?_events';

    protected       $queryBase = 'SELECT e.*, h.*, e.description AS nameINT, e.id AS id, e.id AS ARRAY_KEY FROM ?_events e';
    protected       $queryOpts = array(
                        'e' => [['h']],
                        'h' => ['j' => ['?_holidays h ON e.holidayId = h.id', true], 'o' => '-e.id ASC']
                    );

    public function __construct($conditions = [])
    {
        parent::__construct($conditions);

        // unseting elements while we iterate over the array will cause the pointer to reset
        $replace = [];

        // post processing
        foreach ($this->iterate() as $__)
        {
            // emulate category
            $sT = $this->curTpl['scheduleType'];
            if (!$this->curTpl['holidayId'])
                $this->curTpl['category'] = 0;
            else if ($sT == 2)
                $this->curTpl['category'] = 3;
            else if (in_array($sT, [0, 1]))
                $this->curTpl['category'] = 2;
            else if ($sT == -1)
                $this->curTpl['category'] = 1;

            // preparse requisites
            if ($this->curTpl['requires'])
                $this->curTpl['requires'] = explode(' ', $this->curTpl['requires']);

            // change Ids if holiday is set
            if ($this->curTpl['holidayId'] > 0)
            {
                $this->curTpl['name'] = $this->getField('name', true);
                $replace[$this->id]   = $this->curTpl;
            }
            else                                            // set a name if holiday is missing
            {
                // template
                $this->curTpl['name_loc0']  = $this->curTpl['nameINT'];
                $this->curTpl['iconString'] = 'trade_engineering';
                $this->curTpl['name']       = '(SERVERSIDE) '.$this->getField('nameINT', true);
                $replace[$this->id]         = $this->curTpl;
            }
        }

        foreach ($replace as $old => $data)
        {
            unset($this->templates[$old]);
            $this->templates[$data['id']] = $data;
        }
    }

    public static function getName($id)
    {
        $row = DB::Aowow()->SelectRow('
            SELECT
                IFNULL(h.name_loc0, e.description) AS name_loc0,
                h.name_loc2,
                h.name_loc3,
                h.name_loc4,
                h.name_loc6,
                h.name_loc8
            FROM
                ?_events e
            LEFT JOIN
                ?_holidays h ON e.holidayId = h.id
            WHERE
                e.id = ?d',
            $id
        );

        return Util::localizedString($row, 'name');
    }

    public static function updateDates($date = null)
    {
        if (!$date || empty($date['firstDate']) || empty($date['length']))
        {
            return array(
                'start' => 0,
                'end'   => 0,
                'rec'   => 0
            );
        }

        // Convert everything to seconds
        $firstDate = intVal($date['firstDate']);
        $lastDate  = !empty($date['lastDate']) ? intVal($date['lastDate']) : 5000000000;    // in the far far FAR future..;
        $interval  = !empty($date['rec']) ? intVal($date['rec']) : -1;
        $length    = intVal($date['length']);

        $curStart  = $firstDate;
        $curEnd    = $firstDate + $length;
        $nextStart = $curStart  + $interval;
        $nextEnd   = $curEnd    + $interval;

        while ($interval > 0 && $nextEnd <= $lastDate && $curEnd < time())
        {
            $curStart  = $nextStart;
            $curEnd    = $nextEnd;
            $nextStart = $curStart + $interval;
            $nextEnd   = $curEnd   + $interval;
        }

        return array(
            'start' => $curStart,
            'end'   => $curEnd,
            'rec'   => $interval
        );
    }

    public function getListviewData($forNow = false)
    {
        $data = [];

        foreach ($this->iterate() as $__)
        {
            $data[$this->id] = array(
                'category'  => $this->curTpl['category'],
                'id'        => $this->id,
                'name'      => $this->getField('name', true),
                '_date'     => array(
                    'rec'       => $this->curTpl['occurence'],
                    'length'    => $this->curTpl['length'],
                    'firstDate' => $this->curTpl['startTime'],
                    'lastDate'  => $this->curTpl['endTime']
                )
            );
        }

        if ($forNow)
        {
            foreach ($data as &$d)
            {
               $u = self::updateDates($d['_date']);
               unset($d['_date']);
               $d['startDate'] = $u['start'];
               $d['endDate']   = $u['end'];
               $d['rec']       = $u['rec'];
            }
        }

        return $data;
    }

    public function getJSGlobals($addMask = 0)
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[TYPE_WORLDEVENT][$this->id] = ['name' => $this->getField('name', true), 'icon' => $this->curTpl['iconString']];

        return $data;
    }

    public function renderTooltip()
    {
        if (!$this->curTpl)
            return null;

        $x = '<table><tr><td>';

        // head                 v that extra % is nesecary because we are using sprintf later on
        $x .= '<table width="100%%"><tr><td><b>'.$this->getField('name', true).'</b></td><th><b class="q0">'.Lang::event('category', $this->getField('category')).'</b></th></tr></table>';

        // use string-placeholder for dates
        // start
        $x .= Lang::event('start').Lang::main('colon').'%s<br>';
        // end
        $x .= Lang::event('end').Lang::main('colon').'%s';

        $x .= '</td></tr></table>';

        // desc
        if ($this->getField('holidayId'))
            if ($_ = $this->getField('description', true))
                $x .= '<table><tr><td><span class="q">'.$_.'</span></td></tr></table>';

        return $x;
    }
}

?>
