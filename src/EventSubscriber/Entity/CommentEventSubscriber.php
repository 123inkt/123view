<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber\Entity;

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Utility\Assert;

class CommentEventSubscriber implements EventSubscriberInterface
{
    /**
     * @inheritDoc
     */
    public function getSubscribedEvents(): array
    {
        return [
            Events::postRemove => ['updateMentions'],
            Events::postUpdate => ['updateMentions'],
        ];
    }

    public function updateMentions(LifecycleEventArgs $args): void
    {
        $object = $args->getObject();
        if ($object instanceof Comment) {
            $comment = $object;
        } elseif ($object instanceof CommentReply) {
            $comment = Assert::notNull($object->getComment());
        } else {
            return;
        }

        // update mentions
        $test = true;
    }
}
