<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\Entity\Review\Comment;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Override;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<Comment>
 */
readonly class DeleteCommentProvider implements ProviderInterface
{
    public function __construct(
        private CommentRepository $commentRepository,
        private UserEntityProvider $userProvider,
        private CommentVisibility $commentVisibility,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    #[Override]
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): Comment
    {
        Assert::isInstanceOf($operation, Delete::class, 'Only Delete operation is supported.');

        $comment = $this->commentRepository->find((int)Assert::numeric($uriVariables['id']));
        if ($comment === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        // Let operation security produce the normal unauthenticated response.
        $user = $this->userProvider->getUser();
        if ($user !== null && $this->commentVisibility->isVisible($comment, $user) === false) {
            throw new NotFoundHttpException('Comment not found.');
        }

        return $comment;
    }
}
