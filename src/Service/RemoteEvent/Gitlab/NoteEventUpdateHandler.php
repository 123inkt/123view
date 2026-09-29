<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventUpdateHandler implements RemoteEventHandlerInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @phpstan-impure
     */
    public function supports(object $event): bool
    {
        return $event instanceof NoteEvent && $event->action === 'update' && $event->noteType === 'MergeRequest';
    }

    /**
     * @phpstan-param NoteEvent $event
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);
        $mergeRequest = Assert::notNull($event->mergeRequest);
        $referenceId  = sprintf('%d:%s:%d', $mergeRequest->mergeRequestIId, $event->discussionId, $event->id);

        // handle as comment
        $comment = $this->commentRepository->findOneBy(['extReferenceId' => $referenceId]);
        if ($comment !== null) {
            $this->handleComment($event, $comment, $referenceId);

            return;
        }

        $reply = $this->replyRepository->findOneBy(['extReferenceId' => $referenceId]);
        if ($reply !== null) {
            $this->handleReply($event, $reply, $referenceId);

            return;
        }

        $this->eventLogger->logCommentNotFound($event, $referenceId);
    }

    private function handleComment(NoteEvent $event, Comment $comment, string $referenceId): void
    {
        $state = $event->resolvedAt === null ? CommentStateEnum::Open : CommentStateEnum::Resolved;
        if ($comment->getMessage() === $event->note && $comment->getState() === $state) {
            $this->eventLogger->logCommentUnchanged($event, $referenceId);

            return;
        }

        if ($comment->getMessage() !== $event->note) {
            $comment->setMessage($event->note);
            $this->eventLogger->logCommentMessageUpdated($event, $referenceId);
        }

        if ($comment->getState() !== $state) {
            $comment->setState($state);
            $this->eventLogger->logCommentStateUpdated($event, $referenceId, $state);
        }

        $comment->setModifiedBy(CommentModificationEnum::Gitlab);
        $comment->setUpdateTimestamp($this->now()->getTimestamp());
        $this->commentRepository->save($comment, true);
    }

    private function handleReply(NoteEvent $event, CommentReply $reply, string $referenceId): void
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
