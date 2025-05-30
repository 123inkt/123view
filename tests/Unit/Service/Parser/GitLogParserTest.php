<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Parser;

use DR\Review\Entity\Git\Diff\DiffFile;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Service\CommitHydrator;
use DR\Review\Service\Git\Log\FormatPatternFactory;
use DR\Review\Service\Parser\DiffParser;
use DR\Review\Service\Parser\GitLogParser;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use ValueError;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(GitLogParser::class)]
class GitLogParserTest extends AbstractTestCase
{
    private GitLogParser                    $parser;
    private DiffParser&MockObject           $diffParser;
    private CommitHydrator&MockObject       $hydrator;
    private FormatPatternFactory&MockObject $patternFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->patternFactory = $this->createMock(FormatPatternFactory::class);
        $this->hydrator       = $this->createMock(CommitHydrator::class);
        $this->diffParser     = $this->createMock(DiffParser::class);
        $this->parser         = new GitLogParser($this->patternFactory, $this->hydrator, $this->diffParser);
    }

    public function testGetPattern(): void
    {
        $this->patternFactory->expects($this->once())->method('createPattern')->willReturn('foobar');
        static::assertSame('foobar', $this->parser->getPattern());
    }

    /**
     * @throws Exception
     */
    public function testParseIncorrectPatternParts(): void
    {
        // commit
        $commitLog  = FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog  .= implode(FormatPatternFactory::PARTS_DELIMITER, ["foo", "bar"]);
        $repository = new Repository();

        // test it
        $this->expectException(ValueError::class);
        $this->expectExceptionMessage('array_combine');
        $this->parser->parse($repository, $commitLog);
    }

    /**
     * @throws Exception
     */
    public function testParseSingleCommit(): void
    {
        // commit
        $commitLog  = FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog  .= implode(FormatPatternFactory::PARTS_DELIMITER, self::generateData('commit-part%d', 10));
        $repository = new Repository();
        $files      = [new DiffFile()];
        $commit     = $this->createCommit(null, $files);

        // prepare mocks
        $this->diffParser->expects($this->once())->method('parse')->with('commit-part10')->willReturn($files);
        $this->hydrator->expects($this->once())
            ->method('hydrate')
            ->with($repository, static::callback(static fn($value) => is_array($value)), $files)
            ->willReturn($commit);

        // test it
        $commits = $this->parser->parse($repository, $commitLog);
        static::assertSame([$commit], $commits);
    }

    /**
     * @throws Exception
     */
    public function testParseMultiCommit(): void
    {
        // commit
        $commitLog = FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog .= implode(FormatPatternFactory::PARTS_DELIMITER, self::generateData('commitA-part%d', 10));
        $commitLog .= FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog .= implode(FormatPatternFactory::PARTS_DELIMITER, self::generateData('commitB-part%d', 10));

        $repository    = new Repository();
        $commitA       = $this->createCommit();
        $commitA->refs = "remote/refs";
        $commitB       = $this->createCommit();
        $commitB->refs = null;

        // prepare mocks
        $this->diffParser->expects($this->exactly(2))->method('parse')
            ->with(...consecutive(['commitA-part10'], ['commitB-part10']))
            ->willReturn([]);
        $this->hydrator->expects($this->exactly(2))->method('hydrate')->willReturn($commitA, $commitB);

        // test it
        $commits = $this->parser->parse($repository, $commitLog);
        static::assertSame([$commitA, $commitB], $commits);
        static::assertSame("remote/refs", $commitB->refs);
    }

    /**
     * @throws Exception
     */
    public function testParseMultiCommitWithLimit(): void
    {
        // commit
        $commitLog = FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog .= implode(FormatPatternFactory::PARTS_DELIMITER, self::generateData('commitA-part%d', 10));
        $commitLog .= FormatPatternFactory::COMMIT_DELIMITER;
        $commitLog .= implode(FormatPatternFactory::PARTS_DELIMITER, self::generateData('commitB-part%d', 10));

        $commit = $this->createCommit();

        // prepare mocks
        $this->diffParser->expects($this->once())->method('parse')->with('commitA-part10')->willReturn([]);
        $this->hydrator->expects($this->once())->method('hydrate')->willReturn($commit);

        // test it
        $commits = $this->parser->parse(new Repository(), $commitLog, 1);
        static::assertSame([$commit], $commits);
    }

    /**
     * @return string[]
     */
    private static function generateData(string $format, int $count): array
    {
        $result = [];
        for ($i = 0; $i < $count; $i++) {
            $result[] = sprintf($format, $i + 1);
        }

        return $result;
    }
}
