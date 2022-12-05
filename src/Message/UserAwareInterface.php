<?php
declare(strict_types=1);

namespace DR\Review\Message;

interface UserAwareInterface
{
    public function getUserId(): ?int;
}
