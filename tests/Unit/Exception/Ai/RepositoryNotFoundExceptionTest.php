<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\RepositoryNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepositoryNotFoundException::class)]
class RepositoryNotFoundExceptionTest extends AbstractTestCase
{
    public function testToolCallResult(): void
    {
        $exception = new RepositoryNotFoundException('my-repo');

        static::assertSame("No repository named 'my-repo' was found.", $exception->getToolCallResult());
    }
}
