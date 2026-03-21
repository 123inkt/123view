<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Exception\Ai;

use DR\Review\Exception\Ai\CodeReviewFileNotFoundException;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeReviewFileNotFoundException::class)]
class CodeReviewFileNotFoundExceptionTest extends AbstractTestCase
{
    public function testGetToolCallResult(): void
    {
        $exception = new CodeReviewFileNotFoundException('/path/to/file.php', 123);

        static::assertSame('Filepath /path/to/file.php not found in review 123.', $exception->getToolCallResult());
    }
}
