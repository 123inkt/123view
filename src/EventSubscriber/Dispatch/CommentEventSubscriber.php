<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber\Dispatch;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Utils\Assert;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Messenger\Exception\ExceptionInterface;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Contracts\Service\ResetInterface;

#[AsEntityListener(event: Events::postPersist, method: 'commentAdded', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preCommentUpdated', entity: Comment::class)]
#[AsEntityListener(event: Events::postUpdate, method: 'commentUpdated', entity: Comment::class)]
#[AsEntityListener(event: Events::postRemove, method: 'commentRemoved', entity: Comment::class)]
#[AsEventListener(event: KernelEvents::TERMINATE, method: 'finish')]
#[AsEventListener(event: ConsoleEvents::TERMINATE, method: 'finish')]
class CommentEventSubscriber implements ResetInterface
{
    /** @var array<CommentAdded|CommentUpdated|CommentRemoved|CommentUnresolved|CommentResolved> */
    private array $events = [];
    /** @var array<int, mixed[]> */
    private array $updated = [];

    public function __construct(
        private readonly AuthorizationCheckerInterface $security,
        private readonly MessageBusInterface $bus,
        private readonly CommentEventMessageFactory $messageFactory
    ) {
    }

    public function commentAdded(Comment $comment): void
    {
        $this->events[] = $this->messageFactory->createAdded($comment, $this->getUser($comment));
    }

    public function preCommentUpdated(Comment $comment, PreUpdateEventArgs $event): void
    {
        /** @var mixed[] $changeSet */
        $changeSet                                         = $event->getEntityChangeSet();
        $this->updated[Assert::integer($comment->getId())] = $changeSet;
    }

    public function commentUpdated(Comment $comment): void
    {
        $user      = $this->getUser($comment);
        $changeSet = $this->updated[$comment->getId()] ?? null;
        if ($changeSet === null) {
            return;
        }

        if (array_key_exists('message', $changeSet)) {
            $this->events[] = $this->messageFactory->createUpdated($comment, $user, Assert::string($changeSet['message'][0]));
        }

        if (array_key_exists('state', $changeSet) === false) {
            return;
        }

        if ($comment->getState() === CommentStateType::RESOLVED) {
            $this->events[] = $this->messageFactory->createResolved($comment, $user);
        } else {
            $this->events[] = $this->messageFactory->createUnresolved($comment, $user);
        }
    }

    public function commentRemoved(Comment $comment): void
    {
        $user = $this->getUser($comment);
        if ($user->hasId() === false) {
            return;
        }

        $this->events[] = $this->messageFactory->createRemoved($comment, $user);
    }

    /**
     * @throws ExceptionInterface
     */
    public function reset(): void
    {
        $this->finish();
    }

    /**
     * @throws ExceptionInterface
     */
    public function finish(): void
    {
        $events       = $this->events;
        $this->events = [];
        foreach ($events as $event) {
            $this->bus->dispatch($event);
        }
    }

    private function getUser(Comment $comment): User
    {
        $user = $this->security->getUser();

        return $user instanceof User ? $user : $comment->getUser();
    }
}
