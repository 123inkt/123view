<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class NoteEventHandlerLogger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function logCommentAlreadyExists(NoteEvent $event): void
    {
        $this->logger?->info(
            'NoteEventHandler: comment already exists in 123view',
            ['discussionId' => $event->discussionId, 'message' => $event->description]
        );
    }

    public function logGitlabUserNotFound(NoteEvent $event): void
    {
        $this->logger?->info('NoteEventHandler: user {id} not found in gitlab', ['id' => $event->userId, 'discussionId' => $event->discussionId]);
    }

    public function logUserNotFound(NoteEvent $event, GitlabUser $gitlabUser): void
    {
        $this->logger?->info(
            'NoteEventHandler: user {email} not found in 123view',
            ['email' => $gitlabUser->email, 'discussionId' => $event->discussionId]
        );
    }

    public function logRepositoryNotFound(NoteEvent $event): void
    {
        $this->logger?->info(
            'NoteEventHandler: repository {id} doesnt exist or is inactive in 123view',
            ['id' => $event->projectId, 'discussionId' => $event->discussionId]
        );
    }

    public function logRevisionsNotFound(NoteEvent $event): void
    {
        $this->logger?->info(
            'NoteEventHandler: no revisions found for branch {name}',
            ['name' => $event->sourceBranch, 'discussionId' => $event->discussionId]
        );
    }

    public function logRevisionForFilenameNotFound(NoteEvent $event): void
    {
        $this->logger?->info(
            'NoteEventHandler: no revision matching file {file}',
            ['file' => $event->newPath ?? $event->oldPath, 'discussionId' => $event->discussionId]
        );
    }

    public function logCommentAddedSuccess(NoteEvent $event, CodeReview $review, User $user): void
    {
        $this->logger?->info(
            'NoteEventHandler: creating comment for {file} on {repository}: {review} by {user}',
            [
                'file'         => $event->newPath ?? $event->oldPath,
                'repository'   => $review->getRepository()->getDisplayName(),
                'review'       => 'CR-' . $review->getProjectId(),
                'user'         => $user->getName(),
                'discussionId' => $event->discussionId
            ]
        );
    }
}
