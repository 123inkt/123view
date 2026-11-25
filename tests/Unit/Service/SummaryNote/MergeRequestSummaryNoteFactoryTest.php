<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\SummaryNote;

use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Model\Api\Gitlab\User;
use DR\Review\Model\SummaryNote\SummaryNoteStatus;
use DR\Review\Service\Markdown\MarkdownConverterService;
use DR\Review\Service\SummaryNote\MergeRequestSummaryNoteFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Throwable;

#[CoversClass(MergeRequestSummaryNoteFactory::class)]
class MergeRequestSummaryNoteFactoryTest extends AbstractTestCase
{
    private const SUCCESS_PATTERN  = '/^Result: (passed)$/m';
    private const WARNING_PATTERN  = '/^Result: (\d+ failed)$/m';
    private const PROGRESS_PATTERN = '/^Result: (in progress)$/m';
    private const MR_URL           = 'https://gitlab.example.com/mr/1';

    private MarkdownConverterService&MockObject $markdownConverter;
    private LoggerInterface&MockObject          $patternLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->markdownConverter = $this->createMock(MarkdownConverterService::class);
        $this->patternLogger     = $this->createMock(LoggerInterface::class);
    }

    /**
     * @throws Throwable
     */
    public function testCreateSuccess(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->with("## Summary\nResult: passed")->willReturn('<h2>html</h2>');

        $summaryNote = $this->createFactory()->create($this->createNote(5, "## Summary\nResult: passed"), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Success, $summaryNote->status);
        static::assertSame('passed', $summaryNote->title);
        static::assertSame('<h2>html</h2>', $summaryNote->html);
        static::assertSame(self::MR_URL . '#note_5', $summaryNote->url);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWarning(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory()->create($this->createNote(5, "## Summary\nResult: 3 failed"), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Warning, $summaryNote->status);
        static::assertSame('3 failed', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateProgress(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory()->create($this->createNote(5, "## Summary\nResult: in progress"), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Progress, $summaryNote->status);
        static::assertSame('in progress', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutMatchUsesAuthorName(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory()->create($this->createNote(5, "## Summary\nResult: running"), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Unknown, $summaryNote->status);
        static::assertSame('Summary bot', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutPatterns(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory('', '', '')->create($this->createNote(5, 'Result: passed'), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Unknown, $summaryNote->status);
        static::assertSame('Summary bot', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutCaptureGroup(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory('/passed/')->create($this->createNote(5, 'Result: passed'), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Success, $summaryNote->status);
        static::assertSame('Summary bot', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithEmptyCaptureGroup(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory('/Result: (\d+ skipped)?(passed)/')->create($this->createNote(5, 'Result: passed'), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Success, $summaryNote->status);
        static::assertSame('Summary bot', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithInvalidPattern(): void
    {
        $this->patternLogger->expects($this->once())->method('warning')
            ->with(
                'Invalid summary note pattern: {pattern}',
                static::callback(static fn(array $context) => $context['pattern'] === 'Result: passed')
            );
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $summaryNote = $this->createFactory('Result: passed')->create($this->createNote(5, "## Summary\nResult: 3 failed"), self::MR_URL);

        static::assertSame(SummaryNoteStatus::Warning, $summaryNote->status);
        static::assertSame('3 failed', $summaryNote->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateWithoutAuthor(): void
    {
        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->willReturn('html');

        $note       = new Note();
        $note->id   = 5;
        $note->body = 'text';

        static::assertSame('Summary', $this->createFactory()->create($note, self::MR_URL)->title);
    }

    /**
     * @throws Throwable
     */
    public function testCreateRewritesOnlyNoteLinks(): void
    {
        $body       = "[a](#note_1) [b](https://other.example.com/#note_2) [c](#top)";
        $convertArg = "[a](https://gitlab.example.com/mr/1#note_1) [b](https://other.example.com/#note_2) [c](#top)";

        $this->patternLogger->expects($this->never())->method('warning');
        $this->markdownConverter->expects($this->once())->method('convert')->with($convertArg)->willReturn('html');

        $this->createFactory()->create($this->createNote(9, $body), self::MR_URL);
    }

    private function createFactory(
        string $successPattern = self::SUCCESS_PATTERN,
        string $warningPattern = self::WARNING_PATTERN,
        string $progressPattern = self::PROGRESS_PATTERN
    ): MergeRequestSummaryNoteFactory {
        return new MergeRequestSummaryNoteFactory($successPattern, $warningPattern, $progressPattern, $this->markdownConverter, $this->patternLogger);
    }

    private function createNote(int $id, string $body): Note
    {
        $user       = new User();
        $user->name = 'Summary bot';

        $note         = new Note();
        $note->id     = $id;
        $note->body   = $body;
        $note->author = $user;

        return $note;
    }
}
