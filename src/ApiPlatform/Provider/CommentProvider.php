<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<CommentOutput>
 */
class CommentProvider implements ProviderInterface
{
    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentVisibility $commentVisibility,
        private readonly CommentOutputFactory $commentOutputFactory,
    ) {
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CommentOutput
    {
        if ($operation instanceof Get === false) {
            throw new InvalidArgumentException('Only Get operation is supported');
        }

        $id = $uriVariables['id'] ?? null;
        if (is_int($id) === false && (is_string($id) === false || preg_match('/^\d+$/D', $id) !== 1)) {
            throw new InvalidArgumentException('Comment id must be numeric');
        }

        $comment = $this->commentRepository->find((int)$id);
        if ($comment === null) {
            throw new NotFoundHttpException();
        }

        $user = $this->userProvider->getCurrentUser();
        if ($this->commentVisibility->isVisible($comment, $user) === false) {
            throw new NotFoundHttpException();
        }

        return $this->commentOutputFactory->create($comment);
    }
}
