<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\SummaryNote;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\User;
use DR\Review\Model\SummaryNote\MergeRequestSummaryNote;
use DR\Review\Model\SummaryNote\SummaryNoteStatus;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\SummaryNote\MergeRequestSummaryNoteFactory;
use DR\Review\Service\SummaryNote\MergeRequestSummaryNoteService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(MergeRequestSummaryNoteService::class)]
class MergeRequestSummaryNoteServiceTest extends AbstractTestCase
{
    private const MERGE_REQUEST = ['id' => 1, 'iid' => 2, 'title' => 'title', 'web_url' => 'mr-url'];

    private GitlabService&MockObject                  $gitlabService;
    private CodeReviewRevisionService&MockObject      $revisionService;
    private MergeRequestSummaryNoteFactory&MockObject $summaryNoteFactory;
    private CodeReview                                $review;

    protected function setUp(): void
    {
        parent::setUp();
        $this->gitlabService   = $this->createMock(GitlabService::class);
        $this->revisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->summaryNoteFactory  = $this->createMock(MergeRequestSummaryNoteFactory::class);

        $repository = new Repository();
        $repository->setRepositoryProperty(new RepositoryProperty('gitlab-project-id', '123'));
        $this->review = new CodeReview();
        $this->review->setRepository($repository);
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteDisabledWithoutAuthor(): void
    {
        $this->gitlabService->expects($this->never())->method('getMergeRequest');
        $this->gitlabService->expects($this->never())->method('getMergeRequestNotes');
        $this->summaryNoteFactory->expects($this->never())->method('create');
        $this->revisionService->expects($this->never())->method('findRemoteRef');

        static::assertNull($this->createService('url', '')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutGitlabUrl(): void
    {
        $this->gitlabService->expects($this->never())->method('getMergeRequest');
        $this->gitlabService->expects($this->never())->method('getMergeRequestNotes');
        $this->summaryNoteFactory->expects($this->never())->method('create');
        $this->revisionService->expects($this->never())->method('findRemoteRef');

        static::assertNull($this->createService('', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutProjectId(): void
    {
        $this->gitlabService->expects($this->never())->method('getMergeRequest');
        $this->gitlabService->expects($this->never())->method('getMergeRequestNotes');
        $this->summaryNoteFactory->expects($this->never())->method('create');
        $this->revisionService->expects($this->never())->method('findRemoteRef');

        $review = new CodeReview();
        $review->setRepository(new Repository());

        static::assertNull($this->createService('url', 'summary-bot')->getSummaryNote($review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutRemoteRef(): void
    {
        $this->revisionService->expects($this->once())->method('findRemoteRef')->with($this->review)->willReturn(null);
        $this->gitlabService->expects($this->never())->method('getMergeRequest');
        $this->gitlabService->expects($this->never())->method('getMergeRequestNotes');
        $this->summaryNoteFactory->expects($this->never())->method('create');

        static::assertNull($this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutMergeRequest(): void
    {
        $this->setRemoteRef('remote-ref');
        $this->gitlabService->expects($this->once())->method('getMergeRequest')->with(123, 'remote-ref')->willReturn(null);
        $this->gitlabService->expects($this->never())->method('getMergeRequestNotes');
        $this->summaryNoteFactory->expects($this->never())->method('create');

        static::assertNull($this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutMatchingAuthor(): void
    {
        $this->setRemoteRef('remote-ref');
        $this->gitlabService->expects($this->once())->method('getMergeRequest')->with(123, 'remote-ref')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')->with(123, 2)
            ->willReturn([$this->createNote(1, 'someone-else', '## Summary')]);
        $this->summaryNoteFactory->expects($this->never())->method('create');

        static::assertNull($this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNote(): void
    {
        $this->setRemoteRef('remote-ref');
        $note    = $this->createNote(7, 'summary-bot', '## Summary');
        $summary = new MergeRequestSummaryNote(SummaryNoteStatus::Success, 'no new findings', 'html', 'url');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->with(123, 'remote-ref')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')->with(123, 2)
            ->willReturn([$this->createNote(1, 'someone-else'), $note]);
        $this->summaryNoteFactory->expects($this->once())->method('create')->with($note, 'mr-url')->willReturn($summary);

        static::assertSame($summary, $this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteTakesMostRecentSummary(): void
    {
        $this->setRemoteRef('remote-ref');
        $latestNote = $this->createNote(9, 'summary-bot', '## Summary latest');
        $olderNote  = $this->createNote(3, 'summary-bot', '## Summary older');
        $summary    = new MergeRequestSummaryNote(SummaryNoteStatus::Warning, '1 new finding', 'html', 'url');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')->willReturn([$latestNote, $olderNote]);
        $this->summaryNoteFactory->expects($this->once())->method('create')->with($latestNote, 'mr-url')->willReturn($summary);

        static::assertSame($summary, $this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteSkipsOtherBotNotes(): void
    {
        $this->setRemoteRef('remote-ref');
        $summaryNote = $this->createNote(9, 'summary-bot', '## Summary');
        $summary     = new MergeRequestSummaryNote(SummaryNoteStatus::Warning, '1 new finding', 'html', 'url');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')
            ->willReturn([$this->createNote(5, 'summary-bot', '## Tracking this merge request'), $summaryNote]);
        $this->summaryNoteFactory->expects($this->once())->method('create')->with($summaryNote, 'mr-url')->willReturn($summary);

        static::assertSame($summary, $this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteSkipsNonCommentNotes(): void
    {
        $this->setRemoteRef('remote-ref');
        $systemNote         = $this->createNote(11, 'summary-bot', 'approved this merge request');
        $systemNote->system = true;
        $inlineNote           = $this->createNote(10, 'summary-bot', 'inline finding');
        $inlineNote->position = new Position();
        $latestNote           = $this->createNote(9, 'summary-bot', 'summary');
        $summaryNote          = new MergeRequestSummaryNote(SummaryNoteStatus::Unknown, 'Summary bot', 'html', 'url');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')->willReturn([$systemNote, $inlineNote, $latestNote]);
        $this->summaryNoteFactory->expects($this->once())->method('create')->with($latestNote, 'mr-url')->willReturn($summaryNote);

        static::assertSame($summaryNote, $this->createService('url', 'summary-bot', '')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithOnlyStatusNote(): void
    {
        $this->setRemoteRef('remote-ref');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')
            ->willReturn([$this->createNote(5, 'summary-bot', '## Did not review this merge request')]);
        $this->summaryNoteFactory->expects($this->never())->method('create');

        static::assertNull($this->createService('url', 'summary-bot')->getSummaryNote($this->review));
    }

    /**
     * @throws Throwable
     */
    public function testGetSummaryNoteWithoutPrefix(): void
    {
        $this->setRemoteRef('remote-ref');
        $latestNote  = $this->createNote(9, 'summary-bot', 'any text');
        $summaryNote = new MergeRequestSummaryNote(SummaryNoteStatus::Unknown, 'Summary bot', 'html', 'url');

        $this->gitlabService->expects($this->once())->method('getMergeRequest')->willReturn(self::MERGE_REQUEST);
        $this->gitlabService->expects($this->once())->method('getMergeRequestNotes')
            ->willReturn([$this->createNote(10, 'someone-else', 'any text'), $latestNote, $this->createNote(3, 'summary-bot', 'older')]);
        $this->summaryNoteFactory->expects($this->once())->method('create')->with($latestNote, 'mr-url')->willReturn($summaryNote);

        static::assertSame($summaryNote, $this->createService('url', 'summary-bot', '')->getSummaryNote($this->review));
    }

    private function setRemoteRef(string $remoteRef): void
    {
        $this->revisionService->expects($this->once())->method('findRemoteRef')->with($this->review)->willReturn($remoteRef);
    }

    private function createNote(int $id, string $username, string $body = ''): Note
    {
        $user           = new User();
        $user->username = $username;

        $note         = new Note();
        $note->id     = $id;
        $note->body   = $body;
        $note->author = $user;

        return $note;
    }

    private function createService(string $gitlabApiUrl, string $author, string $prefix = '## Summary'): MergeRequestSummaryNoteService
    {
        return new MergeRequestSummaryNoteService(
            $gitlabApiUrl,
            $author,
            $prefix,
            $this->gitlabService,
            $this->revisionService,
            $this->summaryNoteFactory,
        );
    }
}
