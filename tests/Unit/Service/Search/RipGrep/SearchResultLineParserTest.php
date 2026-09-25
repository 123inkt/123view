<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use ArrayIterator;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Model\Search\SearchResultLine;
use DR\Review\Model\Search\SearchResultLineTypeEnum;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;
use DR\Review\Service\Search\RipGrep\SearchResultFactory;
use DR\Review\Service\Search\RipGrep\SearchResultLineFactory;
use DR\Review\Service\Search\RipGrep\SearchResultLineParser;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @phpstan-import-type SearchResultEntry from JsonDecodeIterator
 */
#[CoversClass(SearchResultLineParser::class)]
class SearchResultLineParserTest extends AbstractTestCase
{
    private SearchResultFactory&MockObject     $resultFactory;
    private SearchResultLineFactory&MockObject $resultLineFactory;
    private SearchResultLineParser             $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->resultFactory     = $this->createMock(SearchResultFactory::class);
        $this->resultLineFactory = $this->createMock(SearchResultLineFactory::class);
        $this->parser            = new SearchResultLineParser('/cache/', $this->resultFactory, $this->resultLineFactory);
    }

    public function testParse(): void
    {
        $repository = new Repository();
        /** @var iterable<int, SearchResultEntry> $iterator */
        $iterator = new ArrayIterator(
            [
                ['type' => 'end'],
                ['type' => 'begin', 'data' => ['path' => ['text' => 'filepath']]],
                ['type' => 'context'],
                ['type' => 'match'],
                ['type' => 'end'],
            ]
        );

        $searchResult = new SearchResult($repository, new SplFileInfo('filepath', '', ''));
        $contextLine  = new SearchResultLine('context', 123, SearchResultLineTypeEnum::Context);
        $matchLine    = new SearchResultLine('match', 456, SearchResultLineTypeEnum::Match);

        $this->resultFactory->expects($this->once())->method('create')->with('filepath', '/cache/', [$repository])->willReturn($searchResult);
        $this->resultLineFactory->expects($this->once())->method('createContextFromEntry')->with(['type' => 'context'])->willReturn($contextLine);
        $this->resultLineFactory->expects($this->once())->method('createMatchFromEntry')->with(['type' => 'match'])->willReturn($matchLine);

        $resultCollection = $this->parser->parse($iterator, [$repository]);
        static::assertCount(1, $resultCollection->results);
        static::assertFalse($resultCollection->moreResultsAvailable);
    }

    public function testParseWithLimit(): void
    {
        $repository = new Repository();
        /** @var iterable<int, SearchResultEntry> $iterator */
        $iterator = new ArrayIterator(
            [
                ['type' => 'begin', 'data' => ['path' => ['text' => 'filepath']]],
                ['type' => 'context'],
                ['type' => 'match'],
                ['type' => 'end'],
                ['type' => 'begin', 'data' => ['path' => ['text' => 'filepath']]],
                ['type' => 'context'],
                ['type' => 'match'],
                ['type' => 'end'],
            ]
        );

        $searchResult = new SearchResult($repository, new SplFileInfo('filepath', '', ''));
        $contextLine  = new SearchResultLine('context', 123, SearchResultLineTypeEnum::Context);
        $matchLine    = new SearchResultLine('match', 456, SearchResultLineTypeEnum::Match);

        $this->resultFactory->expects($this->once())->method('create')->with('filepath', '/cache/', [$repository])->willReturn($searchResult);
        $this->resultLineFactory->expects($this->once())->method('createContextFromEntry')->with(['type' => 'context'])->willReturn($contextLine);
        $this->resultLineFactory->expects($this->once())->method('createMatchFromEntry')->with(['type' => 'match'])->willReturn($matchLine);

        $resultCollection = $this->parser->parse($iterator, [$repository], 1);
        static::assertCount(1, $resultCollection->results);
        static::assertTrue($resultCollection->moreResultsAvailable);
    }
}
