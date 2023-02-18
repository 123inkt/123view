<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\Output;

class UserOutput
{
    /**
     * @codeCoverageIgnore
     */
    public function __construct(public readonly int $id, public readonly string $email)
    {
    }
}
