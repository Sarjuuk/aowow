<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldeventEntry extends DBTypeEntry
{
    public readonly  int       $cuFlags;
    public readonly  int       $category;
    public readonly  LocString $name;
    public readonly  LocString $description;
    public readonly  int       $holidayId;
    public readonly  int       $startTime;
    public readonly  int       $endTime;
    public readonly  int       $occurence;
    public readonly  int       $length;
    /** @var int[] $requires prerequesite events */
    public readonly  array     $requires;
    public readonly ?int       $bossCreature;
    public readonly ?int       $achievementCatOrId;
    public readonly ?int       $looping;
    public readonly ?int       $scheduleType;
    public readonly ?string    $textureString;
    public readonly ?int       $iconId;
    public readonly ?string    $icon;

    public static int    $dbType    = Type::WORLDEVENT;
    public static string $brickFile = 'event';
    public static string $dataTable = '::events';

    public const /* string */ QUERY_BASE = 'SELECT e.`holidayId`, e.`cuFlags`, e.`startTime`, e.`endTime`, e.`occurence`, e.`length`, e.`requires`, e.`description` AS "nameINT", e.`id` AS ARRAY_KEY FROM ::events e';
    public const /* array  */ QUERY_OPTS = array(
        'e'  => [['h', 'ic']],
        'h'  => ['j' => ['::holidays h ON e.`holidayId` = h.`id`', true], 's' => ', h.*, e.id AS "id"', 'o' => '-e.`id` ASC'],
        'ic' => ['j' => ['::icons ic ON ic.`id` = h.`iconId`',     true], 's' => ', ic.`name` AS "icon"']
    );

    public function applyInitData(array $initData) : void
    {
        parent::applyInitData($initData);

        // not linked to holiday > fix name
        if (!$initData['holidayId'])
            $initData['name_loc'.Lang::getLocale()->value] = $initData['nameINT'];

        $this->name        = new LocString($initData, 'name',        pruneFromSrc: true);
        $this->description = new LocString($initData, 'description', pruneFromSrc: true);

        // emulate category
        $this->category = match($initData['scheduleType'])
        {
            -1      => 1,                                   // Holidays
             0, 1   => 2,                                   // Recurring
             2      => 3,                                   // PvP
            default => 0                                    // Uncategorized (holidayId == 0)
        };

        foreach ($initData as $k => $v)
        {
            switch ($k)
            {
                case 'id':                                  // id defined by parent
                    continue 2;
                case 'icon':                                // fix missing icons
                    $this->$k = $v ?: 'trade_engineering';
                    break;
                case 'requires':                            // prepare prerequesite event ids
                    $this->$k = $v ? explode(' ', $v) : [];
                    break;
                default:
                    if (property_exists($this, $k))
                        $this->$k = $v;
            }
        }
    }

    public function getListviewRow(int $addInfoMask = 0x0) : array
    {
        return array(
            'category'  => $this->category,
            'id'        => $this->id,
            'name'      => $this->name,
            '_date'     => array(
                'rec'       => $this->occurence,
                'length'    => $this->length,
                'firstDate' => $this->startTime,
                'lastDate'  => $this->endTime
            )
        );
    }

    public function getJSGlobal(int $addMask = 0) : array
    {
        return [self::$dbType => [$this->id => array(
            'name' => $this->name,
            'icon' => $this->icon
        )]];
    }

    public function renderTooltip() : ?string
    {
        $x = '<table><tr><td>';

        // head                 v that extra % is necessary because we are using sprintf later on
        $x .= '<table width="100%%"><tr><td><b>'.$this->name.'</b></td><th><b class="q0">'.Lang::event('category', $this->category).'</b></th></tr></table>';

        // use string-placeholder for dates
        // start
        $x .= Lang::event('start').'%s<br />';
        // end
        $x .= Lang::event('end').'%s';

        $x .= '</td></tr></table>';

        // desc
        if ($this->holidayId && !$this->description->isEmpty())
            $x .= '<table><tr><td><span class="q">'.$this->description.'</span></td></tr></table>';

        return $x;
    }

    public static function getName(int $id) : ?LocString
    {
        $row = DB::Aowow()->SelectRow(
           'SELECT    IFNULL(h.`name_loc0`, e.`description`) AS "name_loc0", h.`name_loc2`, h.`name_loc3`, h.`name_loc4`, h.`name_loc6`, h.`name_loc8`
            FROM      ::events e
            LEFT JOIN ::holidays h ON e.`holidayId` = h.`id`
            WHERE     e.`id` = %i',
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
            self::updateDates($row['_date'] ?? null, $start, $end, $rec);

            $row['startDate'] = $start ? date(Util::$dateFormatInternal, $start)   : null;
            $row['endDate']   = $end   ? date(Util::$dateFormatInternal, $end - 1) : null;
            $row['rec']       = $rec;

            unset($row['_date']);
        }
    }
}

?>
