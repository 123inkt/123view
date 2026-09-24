<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Security\Voter\CommentVoter;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Override;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @implements ProcessorInterface<mixed, null>
 */
class DeleteCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentVisibility $commentVisibility,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly CommentEventMessageFactory $messageFactory,
        private readonly MessageBusInterface $bus,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    #[Override]
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): null
    {
        Assert::isInstanceOf($operation, Delete::class, 'Only Delete operation is supported.');

        $comment = $this->commentRepository->find((int)Assert::numeric($uriVariables['id']));
        if ($comment === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        $user = $this->userProvider->getCurrentUser();
        if ($this->commentVisibility->isVisible($comment, $user) === false) {
            throw new NotFoundHttpException('Comment not found.');
        }

        if ($this->authorizationChecker->isGranted(CommentVoter::DELETE, $comment) === false) {
            throw new AccessDeniedHttpException('Only the comment author may delete it.');
        }

        /** @var list<object> $messages */
        $messages = [];
        foreach ($comment->getReplies() as $reply) {
            $messages[] = $this->messageFactory->createReplyRemoved($reply, $user);
        }

        $this->commentRepository->remove($comment, true);

        // The delete is committed at this point; a later dispatch failure cannot be rolled back.
        foreach ($messages as $message) {
            $this->bus->dispatch($message);
        }

        return null;
    }
}
