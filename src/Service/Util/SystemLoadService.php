<?php
declare(strict_types=1);

namespace DR\Review\Service\Util;

class SystemLoadService
{
    public function getLoad(): float
    {
        $loadAverage = function_exists('sys_getloadavg') ? sys_getloadavg() : [0, 0, 0];

        return is_array($loadAverage) ? (float)$loadAverage[1] : 0; // system load in the last 5 minutes
    }
}
