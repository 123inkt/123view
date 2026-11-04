<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\ReviewNotFoundForUrlException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewNotFoundForUrlException::class)]
class ReviewNotFoundForUrlExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new ReviewNotFoundForUrlException('my-repo', 42);

        static::assertSame('Review cr-42 not found in repository: my-repo', $exception->getMessage());
    }
}
