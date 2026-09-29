<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentReplyFactory;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Utils\Assert;
use Symfony\Component\Messenger\MessageBusInterface;
use Throwable;

class NoteEventCreateReplyHandler
{
    public function __construct(
        private readonly NoteEventHandlerLogger $eventLogger,
        private readonly CommentReplyFactory $commentReplyFactory,
        private readonly CommentReplyRepository $commentReplyRepository,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(NoteEvent $event, User $user, Comment $comment): void
    {
        $mergeRequest = Assert::notNull($event->mergeRequest);
        $referenceId  = sprintf('%d:%s:%d', $mergeRequest->mergeRequestIId, $event->discussionId, $event->id);
        if ($this->commentReplyRepository->findOneBy(['extReferenceId' => $referenceId]) !== null) {
            $this->eventLogger->logCommentAlreadyExists($event, true);

            return;
        }

        $reply = $this->commentReplyFactory->create($event, $user, $comment);
        $this->commentReplyRepository->save($reply, true);
        $this->bus->dispatch(
            new CommentReplyAdded(
                $comment->getReview()->getId(),
                $reply->getId(),
                $user->getId(),
                $reply->getMessage(),
                $comment->getFilePath(),
                CommentModificationEnum::Gitlab,
            )
        );
        $this->eventLogger->logCommentReplyAddedSuccess($event, $comment->getReview(), $user);
    }
}
