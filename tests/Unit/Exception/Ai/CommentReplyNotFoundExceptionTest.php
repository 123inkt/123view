<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\CommentReplyNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyNotFoundException::class)]
class CommentReplyNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetToolCallResult(): void
    {
        $exception = new CommentReplyNotFoundException(456);

        static::assertSame('Comment reply 456 not found.', $exception->getToolCallResult());
    }
}
