<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Entity\Report;

use DR\Review\Entity\Report\CodeInspectionIssue;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionIssue::class)]
class CodeInspectionIssueTest extends AbstractTestCase
{
    public function testAccessorPairs(): void
    {
        static::assertAccessorPairs(CodeInspectionIssue::class);
    }
}
