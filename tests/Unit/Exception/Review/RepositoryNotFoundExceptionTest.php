<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\RepositoryNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(RepositoryNotFoundException::class)]
class RepositoryNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new RepositoryNotFoundException('my-repo');

        static::assertSame('Repository not found: my-repo', $exception->getMessage());
    }
}
