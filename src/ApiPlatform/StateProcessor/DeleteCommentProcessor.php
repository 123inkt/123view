<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentEventMessageFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Override;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<mixed, null>
 */
class DeleteCommentProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly UserEntityProvider $userProvider,
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
        $comment = Assert::isInstanceOf($data, Comment::class, 'Comment must be loaded before it can be deleted.');
        $user = $this->userProvider->getCurrentUser();

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
