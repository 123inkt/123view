<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\RevisionFilepathMatcher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RevisionFilepathMatcher::class)]
class RevisionFilepathMatcherTest extends AbstractTestCase
{
    private RevisionFileRepository&MockObject $revisionFileRepository;
    private RevisionFilepathMatcher           $matcher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisionFileRepository = $this->createMock(RevisionFileRepository::class);
        $this->matcher                = new RevisionFilepathMatcher($this->revisionFileRepository);
    }

    public function testMatchRevisionPrefersHeadShaOnNewPath(): void
    {
        $event = $this->createEvent('new.php', 'old.php', 'preferred');
        $other     = new Revision()->setCommitHash('other');
        $preferred = new Revision()->setCommitHash('preferred');
        $revisions = [$other, $preferred];

        $this->revisionFileRepository->expects($this->once())
            ->method('findRevisionFileForPath')
            ->with($revisions, 'new.php')
            ->willReturn([
                new RevisionFile()->setRevision($other),
                new RevisionFile()->setRevision($preferred),
            ]);

        static::assertSame([$preferred, 'new.php'], $this->matcher->matchRevision($event, $revisions));
    }

    public function testMatchRevisionFallsBackToOldPath(): void
    {
        $event     = $this->createEvent(null, 'old.php', 'missing');
        $revision  = new Revision()->setCommitHash('commit');
        $revisions = [$revision];

        $this->revisionFileRepository->expects($this->once())
            ->method('findRevisionFileForPath')
            ->willReturnCallback(static function (array $actualRevisions, string $filepath) use ($revisions, $revision): array {
                static::assertSame($revisions, $actualRevisions);

                return $filepath === 'old.php' ? [new RevisionFile()->setRevision($revision)] : [];
            });

        static::assertSame([$revision, 'old.php'], $this->matcher->matchRevision($event, $revisions));
    }

    public function testReturnsNullWhenNoPathMatches(): void
    {
        $event     = $this->createEvent('new.php', 'old.php', 'missing');
        $revision  = new Revision()->setCommitHash('commit');
        $revisions = [$revision];

        $this->revisionFileRepository->expects($this->exactly(2))
            ->method('findRevisionFileForPath')
            ->willReturn([]);

        static::assertSame([null, null], $this->matcher->matchRevision($event, $revisions));
    }

    private function createEvent(?string $newPath, ?string $oldPath, string $headSha): NoteEvent
    {
        $event                   = new NoteEvent();
        $event->position         = new Position();
        $event->position->newPath = $newPath;
        $event->position->oldPath = $oldPath;
        $event->position->headSha = $headSha;

        return $event;
    }
}
