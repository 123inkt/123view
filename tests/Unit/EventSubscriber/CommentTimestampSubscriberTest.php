<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\EventSubscriber\CommentTimestampSubscriber;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\Clock\MockClock;

#[CoversClass(CommentTimestampSubscriber::class)]
class CommentTimestampSubscriberTest extends AbstractTestCase
{
    private const string NOW = '2026-09-29T16:20:01+00:00';

    private CommentTimestampSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriber = new CommentTimestampSubscriber();
        $this->subscriber->setClock(new MockClock(self::NOW));
    }

    public function testSetsCreateAndUpdateTimestampsForComment(): void
    {
        $comment = new Comment();

        $this->subscriber->setCreateTimestamp($comment);

        self::assertSame(new DateTimeImmutable(self::NOW)->getTimestamp(), $comment->getCreateTimestamp()->getTimestamp());
        self::assertSame(new DateTimeImmutable(self::NOW)->getTimestamp(), $comment->getUpdateTimestamp()->getTimestamp());
    }

    public function testSetsCreateAndUpdateTimestampsForReply(): void
    {
        $reply = new CommentReply();

        $this->subscriber->setCreateTimestamp($reply);

        self::assertSame(new DateTimeImmutable(self::NOW)->getTimestamp(), $reply->getCreateTimestamp()->getTimestamp());
        self::assertSame(new DateTimeImmutable(self::NOW)->getTimestamp(), $reply->getUpdateTimestamp()->getTimestamp());
    }

    public function testSetsUpdateTimestampAndRecomputesCommentChangeSet(): void
    {
        $comment = new Comment()->setUpdateTimestamp(new DateTimeImmutable('@123'));

        $this->assertUpdateTimestamp($comment);
    }

    public function testSetsUpdateTimestampAndRecomputesReplyChangeSet(): void
    {
        $reply = new CommentReply();
        $reply->setUpdateTimestamp(new DateTimeImmutable('@123'));

        $this->assertUpdateTimestamp($reply);
    }

    private function assertUpdateTimestamp(Comment|CommentReply $entity): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $unitOfWork    = $this->createMock(UnitOfWork::class);
        $metadata      = new ClassMetadata($entity::class);
        $changeSet     = ['message' => ['old', 'new']];
        $event         = new PreUpdateEventArgs($entity, $entityManager, $changeSet);

        $entityManager
            ->expects($this->once())
            ->method('getUnitOfWork')
            ->willReturn($unitOfWork);
        $entityManager
            ->expects($this->once())
            ->method('getClassMetadata')
            ->with($entity::class)
            ->willReturn($metadata);
        $unitOfWork
            ->expects($this->once())
            ->method('recomputeSingleEntityChangeSet')
            ->with($metadata, $entity);

        $this->subscriber->setUpdateTimestamp($entity, $event);

        self::assertSame(new DateTimeImmutable(self::NOW)->getTimestamp(), $entity->getUpdateTimestamp()->getTimestamp());
    }
}
