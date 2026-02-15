<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Report\CodeInspection;

use ArrayIterator;
use DR\Review\Service\Report\CodeInspection\CodeInspectionIssueParserProvider;
use DR\Review\Service\Report\CodeInspection\Parser\CodeInspectionIssueParserInterface;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CodeInspectionIssueParserProvider::class)]
class CodeInspectionIssueParserProviderTest extends AbstractTestCase
{
    public function testGetFailure(): void
    {
        $provider = new CodeInspectionIssueParserProvider(new ArrayIterator([]));

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unknown inspection: parser');
        $provider->getParser('parser');
    }

    public function testGetParser(): void
    {
        $parser = static::createStub(CodeInspectionIssueParserInterface::class);

        $provider = new CodeInspectionIssueParserProvider(new ArrayIterator(['parser' => $parser]));

        static::assertSame($parser, $provider->getParser('parser'));
    }
}
