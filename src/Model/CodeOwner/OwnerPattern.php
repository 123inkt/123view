<?php
declare(strict_types=1);

namespace DR\Review\Model\CodeOwner;

readonly class OwnerPattern
{
    /**
     * @param list<string> $owners
     */
    public function __construct(public string $pattern, public array $owners)
    {
    }
}
