<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldEventList extends DBTypeList
{
    public static int    $type      = Type::WORLDEVENT;
    public static string $brickFile = 'event';
    public static string $dataTable = '?_events';

    protected string $queryBase = 'SELECT e.`holidayId`, e.`cuFlags`, e.`startTime`, e.`endTime`, e.`occurence`, e.`length`, e.`requires`, e.`description` AS "nameINT", e.`id` AS "eventId", e.`id` AS ARRAY_KEY FROM ?_events e';
    protected array  $queryOpts = array(
                        'e'  => [['h', 'ic']],
                        'h'  => ['j' => ['?_holidays h ON e.`holidayId` = h.`id`', true], 's' => ', h.*', 'o' => '-e.`id` ASC'],
                        'ic' => ['j' => ['?_icons ic ON ic.`id` = h.`iconId`',     true], 's' => ', ic.`name` AS "iconString"']
                    );

    public function __construct(array $conditions = [], array $miscData = [])
    {
        parent::__construct($conditions, $miscData);

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
            $this->templates[$data['eventId']] = $data;
        }
    }

    public static function getName(int $id) : ?LocString
    {
        $row = DB::Aowow()->SelectRow(
           'SELECT    IFNULL(h.`name_loc0`, e.`description`) AS "name_loc0", h.`name_loc2`, h.`name_loc3`, h.`name_loc4`, h.`name_loc6`, h.`name_loc8`
            FROM      ?_events e
            LEFT JOIN ?_holidays h ON e.`holidayId` = h.`id`
            WHERE     e.`id` = ?d',
            $id
        );

        return $row ? new LocString($row) : null;
    }

    public static function updateDates(?array $date = null, ?int &$start = null, ?int &$end = null, ?int &$rec = null) : bool
    {
        if (!$date || empty($date['firstDate']) || empty($date['length']))
            return false;

        $start = $date['firstDate'];
        $end   = $date['firstDate'] + $date['length'];
        $rec   = $date['rec'] ?: -1;                        // interval

        if ($rec < 0 || $date['lastDate'] < time())
            return true;

        $nIntervals = (int)ceil((time() - $end) / $rec);

        $start += $nIntervals * $rec;
        $end   += $nIntervals * $rec;

        return true;
    }

    public static function updateListview(Listview &$listview) : void
    {
        foreach ($listview->iterate() as &$row)
        {
            WorldEventList::updateDates($row['_date'] ?? null, $start, $end, $rec);

            $row['startDate'] = $start ? date(Util::$dateFormatInternal, $start)   : null;
            $row['endDate']   = $end   ? date(Util::$dateFormatInternal, $end - 1) : null;
            $row['rec']       = $rec;

            unset($row['_date']);
        }
    }

    public function getListviewData() : array
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

        return $data;
    }

    public function getJSGlobals(int $addMask = 0) : array
    {
        $data = [];

        foreach ($this->iterate() as $__)
            $data[Type::WORLDEVENT][$this->id] = ['name' => $this->getField('name', true), 'icon' => $this->curTpl['iconString']];

        return $data;
    }

    public function renderTooltip() : ?string
    {
        if (!$this->curTpl)
            return null;

        $x = '<table><tr><td>';

        // head                 v that extra % is nesecary because we are using sprintf later on
        $x .= '<table width="100%%"><tr><td><b>'.$this->getField('name', true).'</b></td><th><b class="q0">'.Lang::event('category', $this->getField('category')).'</b></th></tr></table>';

        // use string-placeholder for dates
        // start
        $x .= Lang::event('start').'%s<br />';
        // end
        $x .= Lang::event('end').'%s';

        $x .= '</td></tr></table>';

        // desc
        if ($this->getField('holidayId'))
            if ($_ = $this->getField('description', true))
                $x .= '<table><tr><td><span class="q">'.$_.'</span></td></tr></table>';

        return $x;
    }
}

?>
