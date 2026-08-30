<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class WorldeventEntry extends DBTypeEntry implements ITooltip
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

    public const string QUERY_BASE = 'SELECT e.`id`, e.`holidayId`, e.`cuFlags`, e.`startTime`, e.`endTime`, e.`occurence`, e.`length`, e.`requires`, e.`description` AS "nameINT", e.`id` AS ARRAY_KEY FROM ::events e';
    public const array  QUERY_OPTS = array(
        'e'  => [['h', 'ic']],
        'h'  => ['j' => ['::holidays h ON e.`holidayId` = h.`id`', true], 's' => ', `bossCreature`, `achievementCatOrId`, `name_loc0`, `name_loc2`, `name_loc3`, `name_loc4`, `name_loc6`, `name_loc8`, `description_loc0`, `description_loc2`, `description_loc3`, `description_loc4`, `description_loc6`, `description_loc8`, `looping`, `scheduleType`, `textureString`, `iconId`', 'o' => '-e.`id` ASC'],
        'ic' => ['j' => ['::icons ic ON ic.`id` = h.`iconId`',     true], 's' => ', ic.`name` AS "icon"']
    );

    public function applyInitData(array $initData, array $opts) : bool
    {
        parent::applyInitData($initData, $opts);

        // not linked to holiday > fix name
        if (!$initData['holidayId'])
            $initData['name_loc'.Lang::getLocale()->value] = $initData['nameINT'];

        $this->cuFlags            = $initData['cuFlags'];
        $this->name               = new LocString($initData, 'name');
        $this->description        = new LocString($initData, 'description');
        $this->holidayId          = $initData['holidayId'];
        $this->startTime          = $initData['startTime'];
        $this->endTime            = $initData['endTime'];
        $this->occurence          = $initData['occurence'];
        $this->length             = $initData['length'];
        $this->requires           = explode(' ', $initData['requires']);
        $this->bossCreature       = $initData['bossCreature'];
        $this->achievementCatOrId = $initData['achievementCatOrId'];
        $this->looping            = $initData['looping'];
        $this->scheduleType       = $initData['scheduleType'];
        $this->textureString      = $initData['textureString'];
        $this->iconId             = $initData['iconId'];
        $this->icon               = $initData['icon'] ?: 'trade_engineering';

        // emulate category
        $this->category = match($initData['scheduleType'])
        {
            -1      => 1,                                   // Holidays
             0, 1   => 2,                                   // Recurring
             2      => 3,                                   // PvP
            default => 0                                    // Uncategorized (holidayId == 0)
        };

        return true;
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

    public function getJSGlobal(int $addMask = GLOBALINFO_SELF) : array
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
