<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Search;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Model\Search\SearchResultLine;
use DR\Review\Model\Search\SearchResultLineTypeEnum;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(SearchResult::class)]
class SearchResultTest extends AbstractTestCase
{
    public function testAccessors(): void
    {
        $result = new SearchResult(new Repository(), new SplFileInfo('file', '', ''));

        $line = new SearchResultLine('line', 123, SearchResultLineTypeEnum::Context);
        $result->addLine($line);

        static::assertSame([$line], $result->getLines());
    }
}
