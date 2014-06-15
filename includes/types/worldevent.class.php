<?php

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldEventList extends BaseType
{
    public static $type      = TYPE_WORLDEVENT;
    public static $brickFile = 'event';

    protected     $queryBase = 'SELECT *, -e.id as id, -e.id AS ARRAY_KEY FROM ?_events e';
    protected     $queryOpts = array(
                                   'e' => ['j' => ['?_holidays h2 ON e.holidayId = h2.id', true], 'o' => '-e.id ASC'],
                                   'h' => ['j' => ['?_holidays h ON e.holidayId = h.id']]
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

            $this->curTpl['eventBak'] = -$this->curTpl['id'];

            // change Ids if holiday is set
            if ($this->curTpl['holidayId'] > 0)
            {
                $this->curTpl['id']   = $this->curTpl['holidayId'];
                $this->curTpl['name'] = $this->getField('name', true);
                $replace[$this->id]   = $this->curTpl;
                unset($this->curTpl['description']);
            }
            else                                            // set a name if holiday is missing
            {
                // template
                $this->curTpl['name_loc0']  = $this->curTpl['description'];
                $this->curTpl['iconString'] = 'trade_engineering';
                $this->curTpl['name']       = '(SERVERSIDE) '.$this->getField('description', true);
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
        if ($id > 0)
            $row = DB::Aowow()->SelectRow('SELECT * FROM ?_holidays WHERE Id = ?d', intVal($id));
        else
            $row = DB::Aowow()->SelectRow('SELECT description as name FROM ?_events WHERE Id = ?d', intVal(-$id));

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

    public function renderTooltip() { }
}

?>
