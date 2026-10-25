<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Service\Api\Gitlab\GitlabCommentResolver;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Service\RemoteEvent\RemoteEventHandlerInterface;
use DR\Review\Service\User\GitlabUserService;
use DR\Utils\Assert;
use Throwable;

/**
 * @implements RemoteEventHandlerInterface<NoteEvent>
 */
readonly class NoteEventCreateHandler implements RemoteEventHandlerInterface
{
    public function __construct(
        private NoteEventHandlerLogger $eventLogger,
        private GitlabUserService $userService,
        private GitlabCommentResolver $commentResolver,
        private NoteEventCreateCommentHandler $commentHandler,
        private NoteEventCreateReplyHandler $replyHandler,
    ) {
    }

    /**
     * @phpstan-impure
     */
    public function supports(object $event): bool
    {
        return $event instanceof NoteEvent && $event->action === 'create' && $event->noteType === 'MergeRequest';
    }

    /**
     * @phpstan-param NoteEvent $event
     * @throws Throwable
     */
    public function handle(object $event): void
    {
        Assert::isInstanceOf($event, NoteEvent::class);
        $mergeRequest = Assert::notNull($event->mergeRequest);
        [$isReply, $comment] = $this->commentResolver->resolve($event->projectId, $mergeRequest->mergeRequestIId, $event->discussionId, $event->id);

        $user = $this->userService->getUser($event->user->id, $event->user->name);
        if ($user === null) {
            $this->eventLogger->logUserNotFound($event, $event->user);

            return;
        }

        if ($isReply) {
            if ($comment !== null) {
                $this->replyHandler->handle($event, $user, $comment);
            }

            return;
        }

        $this->commentHandler->handle($event, $user);
    }
}
