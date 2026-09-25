<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Search\RipGrep;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Model\Search\SearchResultCollection;
use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilder;
use DR\Review\Service\Search\RipGrep\Command\RipGrepCommandBuilderFactory;
use DR\Review\Service\Search\RipGrep\Command\RipGrepProcessExecutor;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\Service\Search\RipGrep\Iterator\JsonDecodeIterator;
use DR\Review\Service\Search\RipGrep\SearchResultLineParser;
use DR\Review\Tests\AbstractTestCase;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GitFileSearcher::class)]
class GitFileSearcherTest extends AbstractTestCase
{
    private RipGrepCommandBuilderFactory&MockObject $commandBuilderFactory;
    private RipGrepProcessExecutor&MockObject       $executor;
    private SearchResultLineParser&MockObject       $parser;
    private GitFileSearcher                         $searcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->commandBuilderFactory = $this->createMock(RipGrepCommandBuilderFactory::class);
        $this->executor              = $this->createMock(RipGrepProcessExecutor::class);
        $this->parser                = $this->createMock(SearchResultLineParser::class);
        $this->searcher              = new GitFileSearcher('/cache/', $this->commandBuilderFactory, $this->executor, $this->parser);
    }

    public function testFindWithExtensions(): void
    {
        $repository       = new Repository();
        $resultCollection = static::createStub(SearchResultCollection::class);
        $iterator         = $this->getIterator();

        $commandBuilder = $this->createMock(RipGrepCommandBuilder::class);
        $commandBuilder->expects($this->once())->method('search')->with('searchQuery')->willReturnSelf();
        $commandBuilder->expects($this->once())->method('glob')->with('*.{json,yaml}')->willReturnSelf();

        $this->commandBuilderFactory->expects($this->once())->method('default')->willReturn($commandBuilder);
        $this->executor->expects($this->once())->method('execute')->with($commandBuilder, '/cache/')->willReturn($iterator);
        $this->parser->expects($this->once())->method('parse')
            ->with(new JsonDecodeIterator($iterator), [$repository], 100)
            ->willReturn($resultCollection);

        static::assertSame($resultCollection, $this->searcher->find('searchQuery', ['json', 'yaml'], [$repository], 100));
    }

    public function testFindWithoutExtensions(): void
    {
        $repository       = new Repository();
        $resultCollection = static::createStub(SearchResultCollection::class);
        $iterator         = $this->getIterator();

        $commandBuilder = $this->createMock(RipGrepCommandBuilder::class);
        $commandBuilder->expects($this->once())->method('search')->with('searchQuery')->willReturnSelf();
        $commandBuilder->expects($this->never())->method('glob');

        $this->commandBuilderFactory->expects($this->once())->method('default')->willReturn($commandBuilder);
        $this->executor->expects($this->once())->method('execute')->with($commandBuilder, '/cache/')->willReturn($iterator);
        $this->parser->expects($this->once())->method('parse')
            ->with(new JsonDecodeIterator($iterator), [$repository], null)
            ->willReturn($resultCollection);

        static::assertSame($resultCollection, $this->searcher->find('searchQuery', null, [$repository]));
    }

    /**
     * @return Generator<int, string>
     */
    private function getIterator(): Generator
    {
        yield '{json-string}';
    }
}
