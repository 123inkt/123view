<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\CommentNotInReviewException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentNotInReviewException::class)]
class CommentNotInReviewExceptionTest extends AbstractTestCase
{
    public function testGetToolCallResult(): void
    {
        $exception = new CommentNotInReviewException(456, 123);

        static::assertSame('Comment 456 does not belong to review 123.', $exception->getToolCallResult());
    }
}
