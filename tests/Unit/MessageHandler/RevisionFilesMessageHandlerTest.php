<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\Revision\RevisionFile;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\RevisionFilesMessageHandler;
use DR\Review\Repository\Revision\RevisionFileRepository;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Service\Git\Diff\GitDiffService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(RevisionFilesMessageHandler::class)]
class RevisionFilesMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject       $revisionRepository;
    private RevisionFileRepository&MockObject   $revisionFileRepository;
    private GitDiffService&MockObject           $gitDiffService;
    private GitRepositoryLockManager&MockObject $lockManager;
    private RevisionFilesMessageHandler         $handler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository     = $this->createMock(RevisionRepository::class);
        $this->revisionFileRepository = $this->createMock(RevisionFileRepository::class);
        $this->gitDiffService         = $this->createMock(GitDiffService::class);
        $this->lockManager            = $this->createMock(GitRepositoryLockManager::class);
        $this->handler                = new RevisionFilesMessageHandler(
            $this->revisionRepository,
            $this->revisionFileRepository,
            $this->gitDiffService,
            $this->lockManager
        );
    }

    public function testInvokeNoRevision(): void
    {
        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->lockManager->expects($this->never())->method('start');
        $this->revisionFileRepository->expects($this->never())->method('save');
        $this->gitDiffService->expects($this->never())->method('getRevisionFiles');

        ($this->handler)(new NewRevisionMessage(123));
    }

    public function testInvokeNoFiles(): void
    {
        $revision   = new Revision();
        $repository = new Repository();
        $revision->setRepository($repository);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->lockManager->expects($this->once())->method('start')->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->gitDiffService->expects($this->once())->method('getRevisionFiles')->with($revision)->willReturn([]);
        $this->revisionFileRepository->expects($this->never())->method('save');

        ($this->handler)(new NewRevisionMessage(123));
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $revision   = (new Revision())->setRepository($repository);
        $file       = new RevisionFile();

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->lockManager->expects($this->once())->method('start')->willReturnCallback(static fn($repository, $callback) => $callback());
        $this->gitDiffService->expects($this->once())->method('getRevisionFiles')->with($revision)->willReturn([$file]);
        $this->revisionFileRepository->expects($this->once())->method('save')->with($file);
        $this->revisionRepository->expects($this->once())->method('save')->with($revision, true);

        ($this->handler)(new NewRevisionMessage(123));
    }
}
