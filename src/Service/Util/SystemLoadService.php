<?php
declare(strict_types=1);

namespace DR\Review\Service\Util;

class SystemLoadService
{
    public function getLoad(): float
    {
        /** @var array{0: float, 1: float, 2: float}|false $loadAverage */
        $loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : [0.0, 0.0, 0.0];

        return is_array($loadAverage) ? $loadAverage[1] : 0.0; // system load in the last 5 minutes
    }
}
