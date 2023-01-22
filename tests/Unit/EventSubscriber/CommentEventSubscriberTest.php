<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\Persistence\ObjectManager;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\EventSubscriber\CommentEventSubscriber;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;

/**
 * @coversDefaultClass \DR\Review\EventSubscriber\CommentEventSubscriber
 * @covers ::__construct
 */
class CommentEventSubscriberTest extends AbstractTestCase
{
    private CommentMentionService&MockObject $mentionService;
    private CommentEventSubscriber           $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mentionService = $this->createMock(CommentMentionService::class);
        $this->service        = new CommentEventSubscriber($this->mentionService);
    }

    /**
     * @covers ::postUpdate
     * @covers ::update
     */
    public function testPostUpdateWithComment(): void
    {
        $comment = new Comment();
        $event   = new LifecycleEventArgs($comment, $this->createMock(ObjectManager::class));

        $this->mentionService->expects(self::once())->method('updateMentions')->with($comment);

        $this->service->postUpdate($event);
    }

    /**
     * @covers ::postUpdate
     * @covers ::update
     */
    public function testPostUpdateWithReply(): void
    {
        $comment = new Comment();
        $reply   = new CommentReply();
        $reply->setComment($comment);
        $event = new LifecycleEventArgs($reply, $this->createMock(ObjectManager::class));

        $this->mentionService->expects(self::once())->method('updateMentions')->with($comment);

        $this->service->postUpdate($event);
    }

    /**
     * @covers ::postUpdate
     * @covers ::update
     */
    public function testPostUpdateWithNonComment(): void
    {
        $object = new stdClass();
        $event  = new LifecycleEventArgs($object, $this->createMock(ObjectManager::class));

        $this->mentionService->expects(self::never())->method('updateMentions');

        $this->service->postUpdate($event);
    }

    /**
     * @covers ::postPersist
     * @covers ::update
     */
    public function testPostPersistWithComment(): void
    {
        $comment = new Comment();
        $event   = new LifecycleEventArgs($comment, $this->createMock(ObjectManager::class));

        $this->mentionService->expects(self::once())->method('updateMentions')->with($comment);

        $this->service->postPersist($event);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $expected = [Events::postPersist, Events::postUpdate];
        $result   = $this->service->getSubscribedEvents();
        static::assertSame($expected, $result);
    }
}
