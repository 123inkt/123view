<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\CodeReviewNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewNotFoundException::class)]
class CodeReviewNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new CodeReviewNotFoundException(456);

        static::assertSame('Code review not found: 456', $exception->getMessage());
    }
}
