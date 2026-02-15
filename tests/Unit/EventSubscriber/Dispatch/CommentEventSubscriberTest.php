<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber\Dispatch;

use Doctrine\ORM\Event\PreUpdateEventArgs;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\EventSubscriber\Dispatch\CommentEventSubscriber;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CommentEventSubscriber::class)]
class CommentEventSubscriberTest extends AbstractTestCase
{
    private UserEntityProvider&MockObject         $userEntityProvider;
    private MessageBusInterface&MockObject        $bus;
    private CommentEventMessageFactory&MockObject $messageFactory;
    private CommentEventSubscriber                $eventSubscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userEntityProvider = $this->createMock(UserEntityProvider::class);
        $this->bus                = $this->createMock(MessageBusInterface::class);
        $this->messageFactory     = $this->createMock(CommentEventMessageFactory::class);
        $this->eventSubscriber    = new CommentEventSubscriber($this->userEntityProvider, $this->bus, $this->messageFactory);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testCommentAdded(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123)->setUser($user);
        $event   = static::createStub(CommentAdded::class);

        $this->userEntityProvider->expects($this->never())->method('getUser');
        $this->messageFactory->expects($this->once())->method('createAdded')->with($comment, $user)->willReturn($event);
        $this->bus->expects($this->once())->method('dispatch')->with($event)->willReturn($this->envelope);

        $this->eventSubscriber->commentAdded($comment);
        $this->eventSubscriber->reset();
        // should only dispatch once
        $this->eventSubscriber->reset();
    }

    public function testCommentUpdatedNoChanges(): void
    {
        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn(new User());
        $this->messageFactory->expects($this->never())->method('createUpdated');
        $this->messageFactory->expects($this->never())->method('createResolved');
        $this->messageFactory->expects($this->never())->method('createUnresolved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->commentUpdated((new Comment())->setId(123));
    }

    public function testCommentNoStateOrMessageChanges(): void
    {
        $event = static::createStub(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['foobar' => ['old', 'new']]);
        $comment = (new Comment())->setId(123);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn(new User());
        $this->messageFactory->expects($this->never())->method('createUpdated');
        $this->messageFactory->expects($this->never())->method('createResolved');
        $this->messageFactory->expects($this->never())->method('createUnresolved');
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentUpdatedMessageAndState(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123)->setState(CommentStateType::RESOLVED);
        $event   = static::createStub(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['message' => ['old', 'new'], 'state' => ['before', 'after']]);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects($this->once())->method('createUpdated')->with($comment, $user, 'old');
        $this->messageFactory->expects($this->once())->method('createResolved')->with($comment, $user);
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentUnresolved(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123)->setState(CommentStateType::OPEN);
        $event   = static::createStub(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['state' => ['before', 'after']]);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects($this->never())->method('createUpdated');
        $this->messageFactory->expects($this->once())->method('createUnresolved')->with($comment, $user);
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentRemoved(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123);

        $this->userEntityProvider->expects($this->once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects($this->once())->method('createRemoved')->with($comment, $user);
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->commentRemoved($comment);
    }

    public function testCommentRemovedWithoutUser(): void
    {
        $comment = (new Comment())->setId(123)->setUser(new User());

        $this->messageFactory->expects($this->never())->method('createRemoved');
        $this->userEntityProvider->expects($this->never())->method('getUser');
        $this->bus->expects($this->never())->method('dispatch');

        $this->eventSubscriber->commentRemoved($comment);
    }
}
