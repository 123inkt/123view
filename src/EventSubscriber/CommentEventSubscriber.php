<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Utility\Assert;

class CommentEventSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly CommentMentionService $mentionService)
    {
    }

    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $this->update($args);
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $this->update($args);
    }

    private function update(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof Comment) {
            $this->mentionService->updateMentions($object);
        } elseif ($object instanceof CommentReply) {
            $this->mentionService->updateMentions(Assert::notNull($object->getComment()));
        }
    }
}
