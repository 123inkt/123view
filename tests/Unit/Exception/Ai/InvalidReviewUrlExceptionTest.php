<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\InvalidReviewUrlException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(InvalidReviewUrlException::class)]
class InvalidReviewUrlExceptionTest extends AbstractTestCase
{
    public function testToolCallResult(): void
    {
        $exception = new InvalidReviewUrlException('not-a-url');

        static::assertSame(
            'Invalid review URL: not-a-url. Expected a URL like https://<host>/app/<repository>/review/cr-<number>.',
            $exception->getToolCallResult()
        );
    }
}
