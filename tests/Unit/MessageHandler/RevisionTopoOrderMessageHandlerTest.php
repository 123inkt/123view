<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\MessageHandler;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Message\Revision\NewRevisionMessage;
use DR\Review\MessageHandler\RevisionTopoOrderMessageHandler;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;
use Throwable;

#[CoversClass(RevisionTopoOrderMessageHandler::class)]
class RevisionTopoOrderMessageHandlerTest extends AbstractTestCase
{
    private RevisionRepository&MockObject     $revisionRepository;
    private EntityManagerInterface&MockObject $entityManager;
    private RevisionTopoOrderMessageHandler   $handler;

    public function setUp(): void
    {
        parent::setUp();
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->entityManager      = $this->createMock(EntityManagerInterface::class);
        $this->handler            = new RevisionTopoOrderMessageHandler($this->revisionRepository, $this->entityManager);
        $this->handler->setLogger(static::createStub(LoggerInterface::class));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithUnknownRevision(): void
    {
        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->revisionRepository->expects($this->never())->method('save');
        $this->revisionRepository->expects($this->never())->method('findBy');
        $this->entityManager->expects($this->never())->method('flush');
        $this->entityManager->expects($this->never())->method('clear');

        ($this->handler)(new NewRevisionMessage(123));
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithNoParentOrChildRevisions(): void
    {
        $repository = new Repository();
        $revision   = new Revision();
        $revision->setId(123);
        $revision->setCommitHash('abc123');
        $revision->setParentHash(null);
        $revision->setCreateTimestamp(1000);
        $revision->setRepository($repository);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->revisionRepository->expects($this->once())->method('save')->with($revision);
        $this->revisionRepository->expects($this->once())
            ->method('findBy')
            ->with(['repository' => $repository, 'parentHash' => 'abc123'])
            ->willReturn([]);
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('clear');

        ($this->handler)(new NewRevisionMessage(123));

        static::assertNotNull($revision->getSort());
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithChildAndParentRevisionsInCorrectOrder(): void
    {
        $repository = new Repository();

        $parentRevision = new Revision();
        $parentRevision->setId(100);
        $parentRevision->setCommitHash('parent123');
        $parentRevision->setCreateTimestamp(1000);
        $parentRevision->setRepository($repository);

        $childRevision = new Revision();
        $childRevision->setId(200);
        $childRevision->setCommitHash('child123');
        $childRevision->setParentHash('abc123');
        $childRevision->setCreateTimestamp(2000);
        $childRevision->setRepository($repository);

        $revision = new Revision();
        $revision->setId(123);
        $revision->setCommitHash('abc123');
        $revision->setParentHash('parent123');
        $revision->setCreateTimestamp(1500);
        $revision->setRepository($repository);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->revisionRepository->expects($this->exactly(3))->method('save');
        $this->revisionRepository->expects($this->exactly(2))
            ->method('findBy')
            ->willReturnCallback(static fn(array $criteria) => match ($criteria) {
                ['repository' => $repository, 'commitHash' => 'parent123'] => [$parentRevision],
                ['repository' => $repository, 'parentHash' => 'abc123']    => [$childRevision],
                default                                                    => []
            });
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('clear');

        ($this->handler)(new NewRevisionMessage(123));

        static::assertNotNull($revision->getSort());
        static::assertNotNull($parentRevision->getSort());
        // child sort is already set from initial revision save flow, not updated because timestamps are correct
        static::assertNotNull($childRevision->getSort());
    }

    /**
     * @throws Throwable
     */
    public function testInvokeWithChildAndParentRevisionsOutOfOrder(): void
    {
        $repository = new Repository();

        // Parent has later timestamp than child - out of order
        $parentRevision = new Revision();
        $parentRevision->setId(100);
        $parentRevision->setCommitHash('parent123');
        $parentRevision->setCreateTimestamp(2000);
        $parentRevision->setRepository($repository);

        // Child has earlier timestamp than parent - out of order
        $childRevision = new Revision();
        $childRevision->setId(200);
        $childRevision->setCommitHash('child123');
        $childRevision->setParentHash('abc123');
        $childRevision->setCreateTimestamp(500);
        $childRevision->setRepository($repository);

        $revision = new Revision();
        $revision->setId(123);
        $revision->setCommitHash('abc123');
        $revision->setParentHash('parent123');
        $revision->setCreateTimestamp(1000);
        $revision->setRepository($repository);

        $this->revisionRepository->expects($this->once())->method('find')->with(123)->willReturn($revision);
        $this->revisionRepository->expects($this->exactly(4))->method('save');
        $this->revisionRepository->expects($this->exactly(2))
            ->method('findBy')
            ->willReturnCallback(static fn(array $criteria) => match ($criteria) {
                ['repository' => $repository, 'commitHash' => 'parent123'] => [$parentRevision],
                ['repository' => $repository, 'parentHash' => 'abc123']    => [$childRevision],
                default                                                    => []
            });
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('clear');

        ($this->handler)(new NewRevisionMessage(123));

        static::assertNotNull($revision->getSort());
        static::assertNotNull($parentRevision->getSort());
        static::assertNotNull($childRevision->getSort());
    }
}
