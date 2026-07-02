<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\CommentNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentNotFoundException::class)]
class CommentNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetToolCallResult(): void
    {
        $exception = new CommentNotFoundException(456);

        static::assertSame('Comment 456 not found.', $exception->getToolCallResult());
    }
}
