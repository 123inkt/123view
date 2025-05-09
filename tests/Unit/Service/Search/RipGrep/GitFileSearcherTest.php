<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResult;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;
use DR\Review\Service\Search\RipGrep\RipGrepProcessExecutor;
use DR\Review\Service\Search\RipGrep\SearchResultLineParser;
use DR\Review\Tests\AbstractTestCase;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitFileSearcher::class)]
class GitFileSearcherTest extends AbstractTestCase
{
    private RipGrepProcessExecutor&MockObject $executor;
    private SearchResultLineParser&MockObject $parser;
    private GitFileSearcher                   $searcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->executor = $this->createMock(RipGrepProcessExecutor::class);
        $this->parser   = $this->createMock(SearchResultLineParser::class);
        $this->searcher = new GitFileSearcher('/cache/', $this->executor, $this->parser);
    }

    public function testFind(): void
    {
        $repository = new Repository();
        $arguments  = [
            '--hidden',
            '--color=never',
            '--line-number',
            '--after-context=5',
            '--before-context=5',
            '--glob=!.git/',
            '--json',
            'searchQuery'
        ];
        $result     = static::createStub(SearchResult::class);
        $iterator   = $this->getIterator();

        $this->executor->expects($this->once())->method('execute')->with($arguments, '/cache/')->willReturn($iterator);
        $this->parser->expects($this->once())->method('parse')->with(new JsonDecodeIterator($iterator), [$repository])->willReturn([$result]);

        static::assertSame([$result], $this->searcher->find('searchQuery', [$repository]));
    }

    /**
     * @return Generator<int, string>
     */
    private function getIterator(): Generator
    {
        yield '{json-string}';
    }
}
