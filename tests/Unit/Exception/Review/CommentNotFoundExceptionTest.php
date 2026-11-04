<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\CommentNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentNotFoundException::class)]
class CommentNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new CommentNotFoundException(456);

        static::assertSame('Comment not found: 456', $exception->getMessage());
    }
}
