<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Report;

use DR\Review\Entity\Report\CodeCoverageFile;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeCoverageFile::class)]
class CodeCoverageFileTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CodeCoverageFile::class);
    }
}
