<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
class NoteEventUpdateHandler implements RemoteEventHandlerInterface
{
    use ClockAwareTrait;

    public function __construct(private readonly NoteEventHandlerLogger $eventLogger, private readonly CommentRepository $commentRepository)
    {
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
        $comment      = $this->commentRepository->findOneBy(['extReferenceId' => $referenceId]);
        if ($comment === null) {
            $this->eventLogger->logCommentNotFound($event, $referenceId);

            return;
        }

        if ($comment->getMessage() === $event->note) {
            $this->eventLogger->logCommentUnchanged($event, $referenceId);

            return;
        }

        $comment->setMessage($event->note);
        $comment->setUpdateTimestamp($this->now()->getTimestamp());
        $this->commentRepository->save($comment, true);
        $this->eventLogger->logCommentUpdated($event, $referenceId);
    }
}
