<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventUpdateHandler implements RemoteEventHandlerInterface
{
    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $replyRepository,
        private readonly NoteEventCommentUpdateHandler $commentUpdateHandler,
        private readonly NoteEventReplyUpdateHandler $replyUpdateHandler,
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
            $this->commentUpdateHandler->handle($event, $comment, $referenceId);

            return;
        }

        $reply = $this->replyRepository->findOneBy(['extReferenceId' => $referenceId]);
        if ($reply !== null) {
            $this->replyUpdateHandler->handle($event, $reply, $referenceId);

            return;
        }

        $this->eventLogger->logCommentNotFound($event, $referenceId);
    }
}
