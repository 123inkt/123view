<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\UnitOfWork;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\EventSubscriber\CommentTimestampSubscriber;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentTimestampSubscriber::class)]
class CommentTimestampSubscriberTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CommentTimestampSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subscriber = new CommentTimestampSubscriber();
    }

    public function testSetsTimestampsForComment(): void
    {
        $comment = new Comment();

        $this->subscriber->setCreateTimestamp($comment);

        self::assertSame(self::now()->getTimestamp(), $comment->getCreateTimestamp()->getTimestamp());
        self::assertSame(self::now()->getTimestamp(), $comment->getUpdateTimestamp()->getTimestamp());
    }

    public function testSetsTimestampsForReply(): void
    {
        $reply = new CommentReply();

        $this->subscriber->setCreateTimestamp($reply);

        self::assertSame(self::now()->getTimestamp(), $reply->getCreateTimestamp()->getTimestamp());
        self::assertSame(self::now()->getTimestamp(), $reply->getUpdateTimestamp()->getTimestamp());
    }

    public function testUpdatesCommentTimestamp(): void
    {
        $comment = new Comment()->setUpdateTimestamp(new DateTimeImmutable('@123'));

        $this->assertUpdateTimestamp($comment);
    }

    public function testUpdatesReplyTimestamp(): void
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

        self::assertSame(self::now()->getTimestamp(), $entity->getUpdateTimestamp()->getTimestamp());
    }
}
