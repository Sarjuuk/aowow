<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class DateTime extends \DateTimeImmutable
{
    // in msec                         yr           mo          w          d         h        m      s      ms
    private const /* array */ RANGE = [31557600000, 2629800000, 604800000, 86400000, 3600000, 60000, 1000,  1];
    private const /* string */ NBSP = "\u{00A0}";           // \u00A0 is usable by js

    public function __construct(int $seconds = 0)
    {
        $datetime = $seconds ? date(DATE_ATOM, $seconds) : 'now';
        parent::__construct($datetime);
    }

    /**
     * Adaptive, human-readable format of a date
     * exact date if larger than 1 month
     * relative days/time if smaller than 1 month
     *
     * @param  int    $timestamp    unix timestamp to display
     * @param  bool   $withTime     [optional] append time on exact dates
     * @return string               adaptive date/time string
     */
    public function formatDate(int $timestamp, bool $withTime = false) : string
    {
        $txt     = '';
        $elapsed = abs($this->getTimestamp() - $timestamp);

        $today    = new DateTime();
        $eventDay = new DateTime(time() - $elapsed);

        $todayMidnight    = $today->setTime(0, 0);
        $eventDayMidnight = $eventDay->setTime(0, 0);

        $delta = $todayMidnight->diff($eventDayMidnight, true)->days;

        if ($elapsed >= 2592000) /* More than a month ago */
            $txt = Lang::main('date_on') . $eventDay->formatDateSimple($withTime);
        else if ($delta > 1)
            $txt = Lang::main('ddaysago', [$delta]);
        else if ($elapsed >= 43200)
        {
            if ($today->format('j') == $eventDay->format('j'))
                $txt = Lang::main('today');
            else
                $txt = Lang::main('yesterday');

            $txt = $eventDay->formatTimeSimple($txt);
        }
        else /* Less than 12 hours ago */
            $txt = Lang::main('date_ago', [self::formatTimeElapsed($elapsed * 1000)]);

        return $txt;
    }

    /**
     * Human-readable format of a date. Optionally append time of day.
     *
     * @param  bool   $withTime [optional] affixes day time
     * @return string           a formatted date string.
     */
    public function formatDateSimple(bool $withTime = false) : string
    {
        $txt   = '';
        $day   = $this->format('d');
        $month = $this->format('m');
        $year  = $this->format('Y');

        if ($year <= 1970)
            $txt .= Lang::main('unknowndate_stc');
        else
            $txt .= Lang::main('date_simple', [$day, $month, $year]);

        if ($withTime)
            $txt = $this->formatTimeSimple($txt);

        return $txt;
    }

    /**
     * Human-readable format of the time of day.
     *
     * @param  string $txt      [optional] text to affeix the day time to
     * @param  bool   $noPrefix [optional] don't use " at " to affix time of day to $txt
     * @return string           a formatted time of day string.
     */
    public function formatTimeSimple(string $txt = '', bool $noPrefix = false) : string
    {
        $hours   = $this->format('G');
        $minutes = $this->format('i');

        $txt .= ($noPrefix ? ' ' : Lang::main('date_at'));

        if ($hours == 12)
            $txt .= Lang::main('noon');
        else if ($hours == 0)
            $txt .= Lang::main('midnight');
        else if ($hours > 12)
            $txt .= ($hours - 12) . ':' . $minutes . ' ' . Lang::main('pm');
        else
            $txt .= $hours . ':' . $minutes . ' ' . Lang::main('am');

        return $txt;
    }

    /**
     * Calculate component values from timestamp
     *
     * @param  int   $msec  time in milliseconds to parse
     * @return int[]        [msec, sec, min, hr, day]
     */
    public static function parse(int $msec) : array
    {
        $time = [0, 0, 0, 0, 0];
        $msec = abs($msec);

        for ($i = 3; $i < count(self::RANGE); ++$i)
        {
            if ($msec < self::RANGE[$i])
                continue;

            $time[7 - $i] = intVal($msec / self::RANGE[$i]);
            $msec        %= self::RANGE[$i];
        }

        return $time;
    }

    /**
     * Human-readable longform format of a timespan.
     *
     * @param  int    $delay    time in milliseconds to format
     * @return string           a formatted time string. If an error occured "n/a" (localized) is returned
     */
    public static function formatTimeElapsedFloat(int $delay) : string
    {
        $delay = max($delay, 1);

        for ($i = 0; $i < count(self::RANGE); ++$i)
        {
            if ($delay < self::RANGE[$i])
                continue;

            $v = round($delay / self::RANGE[$i], 2);
            return $v . self::NBSP . Lang::timeUnits($v === 1.0 ? 'sg' : 'pl', $i);
        }

        return Lang::main('n_a');
    }

    /**
     * Human-readable format of a timespan.
     *
     * @param  int    $delay    time in milliseconds to format
     * @param  int    $maxRange [optional] time unit index - 0 (year) ... 7 (milliseconds)
     * @return string           a formatted time string. If an error occured "n/a" (localized) is returned
     */
    public static function formatTimeElapsed(int $delay, int $maxRange = 3) : string
    {
        if ($maxRange > 7 || $maxRange < 0)
            $maxRange = 3;                                  // default: days

        $subunit = [1, 3, 3, -1, 5, -1, 7, -1];
        $delay   = max($delay, 1);

        for ($i = $maxRange; $i < count(self::RANGE); ++$i)
        {
            if ($delay >= self::RANGE[$i])
            {
                $i1 = $i;
                $v1 = floor($delay / self::RANGE[$i1]);

                if ($subunit[$i1] != -1)
                {
                    $i2     = $subunit[$i1];
                    $delay %= self::RANGE[$i1];
                    $v2     = floor($delay / self::RANGE[$i2]);

                    if ($v2 > 0)
                        return self::OMG($v1, $i1, true) . self::NBSP . self::OMG($v2, $i2, true);
                }

                return self::OMG($v1, $i1, false);
            }
        }

        return Lang::main('n_a');
    }

    /**
     * internal number formatter
     *
     * @param  int    $value    unit value
     * @param  int    $unit     time unit index 0 (year) ... 7 (milliseconds)
     * @param  bool   $abbrv    use abbreviation
     * @return string           value + unit
     */
    private static function OMG(int $value, int $unit, bool $abbrv) : string
    {
        if ($abbrv && !Lang::timeUnits('ab', $unit))
            $abbrv = false;

        return $value .= self::NBSP . match(true)
        {
            $abbrv      => Lang::timeUnits('ab', $unit),
            $value == 1 => Lang::timeUnits('sg', $unit),
            default     => Lang::timeUnits('pl', $unit)
        };
    }
}

?>
