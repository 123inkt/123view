<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;

#[AsEntityListener(event: Events::prePersist, method: 'setCreateTimestamp', entity: Comment::class)]
#[AsEntityListener(event: Events::prePersist, method: 'setCreateTimestamp', entity: CommentReply::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'setUpdateTimestamp', entity: Comment::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'setUpdateTimestamp', entity: CommentReply::class)]
class CommentTimestampSubscriber
{
    use ClockAwareTrait;

    public function setCreateTimestamp(Comment|CommentReply $entity): void
    {
        $timestamp = $this->now();
        $entity->setCreateTimestamp($timestamp);
        $entity->setUpdateTimestamp($timestamp);
    }

    public function setUpdateTimestamp(Comment|CommentReply $entity, PreUpdateEventArgs $event): void
    {
        $entity->setUpdateTimestamp($this->now());

        $entityManager = Assert::isInstanceOf($event->getObjectManager(), EntityManagerInterface::class);
        $entityManager->getUnitOfWork()->recomputeSingleEntityChangeSet($entityManager->getClassMetadata($entity::class), $entity);
    }
}
