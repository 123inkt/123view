<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewNotFoundException::class)]
class CodeReviewNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetToolCallResult(): void
    {
        $exception = new CodeReviewNotFoundException(456);

        static::assertSame('Code review 456 not found.', $exception->getToolCallResult());
    }
}
