<?php
declare(strict_types=1);

namespace DR\Review\Tests\Helper;

class MockCallableClass
{
    public function __invoke(): void
    {
        // nothing
    }
}
