<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\ReviewNotFoundForUrlException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewNotFoundForUrlException::class)]
class ReviewNotFoundForUrlExceptionTest extends AbstractTestCase
{
    public function testToolCallResult(): void
    {
        $exception = new ReviewNotFoundForUrlException('my-repo', 42);

        static::assertSame("No review cr-42 exists in repository 'my-repo'.", $exception->getToolCallResult());
    }
}
