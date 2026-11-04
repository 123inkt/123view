<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Review;

use DR\Review\Exception\Review\CodeReviewFileNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewFileNotFoundException::class)]
class CodeReviewFileNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetMessage(): void
    {
        $exception = new CodeReviewFileNotFoundException();

        static::assertSame('', $exception->getMessage());
    }
}
