<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message\Delay;

/**
 * @codeCoverageIgnore
 */
class DelayableMessage
{
    public function __construct(public readonly object $message)
    {
    }
}
