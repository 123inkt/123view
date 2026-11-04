<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\CommentReplyNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyNotFoundException::class)]
class CommentReplyNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new CommentReplyNotFoundException(456);

        static::assertSame('Comment reply not found: 456', $exception->getMessage());
    }
}
