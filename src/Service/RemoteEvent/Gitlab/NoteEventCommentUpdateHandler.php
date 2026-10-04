<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\GitlabCommentFormatter;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;

class NoteEventCommentUpdateHandler
{
    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly GitlabCommentFormatter $commentFormatter,
        private readonly CommentRepository $commentRepository,
    ) {
    }

    public function handle(NoteEvent $event, Comment $comment, string $referenceId): void
    {
        $state   = $event->resolvedAt === null ? CommentStateEnum::Open : CommentStateEnum::Resolved;
        $message = $this->commentFormatter->format($comment);
        if ($message === $event->note && $comment->getState() === $state) {
            $this->eventLogger->logCommentUnchanged($event, $referenceId);

            return;
        }

        if ($message !== $event->note) {
            $comment->setMessage((string)preg_replace('/<!-- 123view-footer-start -->.*?<!-- 123view-footer-end -->/s', '', $event->note));
            $this->eventLogger->logCommentMessageUpdated($event, $referenceId);
        }

        if ($comment->getState() !== $state) {
            $comment->setState($state);
            $this->eventLogger->logCommentStateUpdated($event, $referenceId, $state);
        }
        $comment->setModifiedBy(CommentModificationEnum::Gitlab);
        $this->commentRepository->save($comment, true);
    }
}
