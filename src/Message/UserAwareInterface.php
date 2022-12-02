<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Message;

interface UserAwareInterface
{
    public function getUserId(): ?int;
}
