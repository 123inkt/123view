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
        $comment = (new Comment())->setId(123);
        $event   = $this->createMock(CommentAdded::class);

        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects(self::once())->method('createAdded')->with($comment, $user)->willReturn($event);
        $this->bus->expects(self::once())->method('dispatch')->with($event)->willReturn($this->envelope);

        $this->eventSubscriber->commentAdded($comment);
        $this->eventSubscriber->reset();
        // should only dispatch once
        $this->eventSubscriber->reset();
    }

    public function testCommentUpdatedNoChanges(): void
    {
        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn(new User());
        $this->messageFactory->expects(self::never())->method('createUpdated');
        $this->messageFactory->expects(self::never())->method('createResolved');
        $this->messageFactory->expects(self::never())->method('createUnresolved');

        $this->eventSubscriber->commentUpdated((new Comment())->setId(123));
    }

    public function testCommentNoStateOrMessageChanges(): void
    {
        $event = $this->createMock(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['foobar' => ['old', 'new']]);
        $comment = (new Comment())->setId(123);

        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn(new User());
        $this->messageFactory->expects(self::never())->method('createUpdated');
        $this->messageFactory->expects(self::never())->method('createResolved');
        $this->messageFactory->expects(self::never())->method('createUnresolved');

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentUpdatedMessageAndState(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123)->setState(CommentStateType::RESOLVED);
        $event   = $this->createMock(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['message' => ['old', 'new'], 'state' => ['before', 'after']]);

        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects(self::once())->method('createUpdated')->with($comment, $user, 'old');
        $this->messageFactory->expects(self::once())->method('createResolved')->with($comment, $user);

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentUnresolved(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123)->setState(CommentStateType::OPEN);
        $event   = $this->createMock(PreUpdateEventArgs::class);
        $event->method('getEntityChangeSet')->willReturn(['state' => ['before', 'after']]);

        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects(self::never())->method('createUpdated');
        $this->messageFactory->expects(self::once())->method('createUnresolved')->with($comment, $user);

        $this->eventSubscriber->preCommentUpdated($comment, $event);
        $this->eventSubscriber->commentUpdated($comment);
    }

    public function testCommentRemoved(): void
    {
        $user    = (new User())->setId(345);
        $comment = (new Comment())->setId(123);

        $this->userEntityProvider->expects(self::once())->method('getUser')->willReturn($user);
        $this->messageFactory->expects(self::once())->method('createRemoved')->with($comment, $user);

        $this->eventSubscriber->commentRemoved($comment);
    }

    public function testCommentRemovedWithoutUser(): void
    {
        $comment = (new Comment())->setId(123)->setUser(new User());

        $this->messageFactory->expects(self::never())->method('createRemoved');

        $this->eventSubscriber->commentRemoved($comment);
    }
}
