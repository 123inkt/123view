<?php
declare(strict_types=1);

namespace DR\Review\Service;

/**
 * @codeCoverageIgnore
 */
class ProcessService
{
    /**
     * @return resource|false
     */
    public function popen(string $command, string $mode): false
    {
        return popen($command, $mode);
    }
}
