<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Search;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Model\Search\SearchResultCollection;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Finder\SplFileInfo;

#[CoversClass(SearchResultCollection::class)]
class SearchResultCollectionTest extends AbstractTestCase
{
    public function testIteratePerRepositoryEmptyCollection(): void
    {
        $collection = new SearchResultCollection([], false);
        $results    = iterator_to_array($collection->iteratePerRepository());
        static::assertCount(0, $results);
    }

    public function testIteratePerRepository(): void
    {
        $repository = (new Repository())->setId(123);
        $result     = new SearchResult($repository, new SplFileInfo('file', '', ''));

        $collection = new SearchResultCollection([$result], false);

        $results = iterator_to_array($collection->iteratePerRepository());
        static::assertSame([123 => [$result]], $results);
    }
}
