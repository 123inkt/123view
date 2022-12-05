<?php
declare(strict_types=1);

namespace DR\Review\Event;

use DR\Review\Entity\Git\Commit;
use Symfony\Contracts\EventDispatcher\Event;

class CommitEvent extends Event
{
    public Commit $commit;

    public function __construct(Commit $commit)
    {
        $this->commit = $commit;
    }
}
