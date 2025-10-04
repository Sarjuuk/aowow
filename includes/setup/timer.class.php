<?php

namespace Aowow;

if (!defined('AOWOW_REVISION'))
    die('illegal access');


class Timer
{
    private float $t_cur = 0;
    private float $t_new = 0;
    private float $intv  = 0;

    public function __construct(int $intervall)
    {
        $this->intv  = $intervall / 1000;                   // in msec
        $this->t_cur = microtime(true);
    }

    public function update() : bool
    {
        $this->t_new = microtime(true);
        if ($this->t_new > $this->t_cur + $this->intv)
        {
            $this->t_cur = $this->t_cur + $this->intv;
            return true;
        }

        return false;
    }

    public function reset() : void
    {
        $this->t_cur = microtime(true) - $this->intv;
    }
}

?>
