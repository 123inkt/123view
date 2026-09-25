<?php
declare(strict_types=1);

namespace DR\Review\Service\Process;

/**
 * @codeCoverageIgnore
 */
class ProcessService
{
    /**
     * @return resource|false
     */
    public function popen(string $command, string $mode)
    {
        return popen($command, $mode);
    }
}
