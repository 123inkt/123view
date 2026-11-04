<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\InvalidReviewUrlException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InvalidReviewUrlException::class)]
class InvalidReviewUrlExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new InvalidReviewUrlException('not-a-url');

        static::assertSame('Invalid review URL: not-a-url', $exception->getMessage());
    }
}
