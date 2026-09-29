<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

class NoteEventReplyUpdateHandler
{
    use ClockAwareTrait;

    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly CommentReplyRepository $replyRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    public function handle(NoteEvent $event, CommentReply $reply, string $referenceId): void
    {
        if ($reply->getMessage() === $event->note) {
            $this->eventLogger->logCommentUnchanged($event, $referenceId, true);

            return;
        }

        $originalMessage = $reply->getMessage();
        $reply->setMessage($event->note);
        $reply->setModifiedBy(CommentModificationEnum::Gitlab);
        $reply->setUpdateTimestamp($this->now()->getTimestamp());
        $this->replyRepository->save($reply, true);

        $this->bus->dispatch(
            new CommentReplyUpdated(
                $reply->getComment()->getReview()->getId(),
                $reply->getId(),
                $reply->getUser()->getId(),
                $originalMessage,
                CommentModificationEnum::Gitlab,
            )
        );

        $this->eventLogger->logCommentMessageUpdated($event, $referenceId, true);
    }
}
