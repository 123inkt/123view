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
use DR\Utils\Assert;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<CommentOutput>
 */
readonly class CommentProvider implements ProviderInterface
{
    public function __construct(
        private CommentRepository $commentRepository,
        private UserEntityProvider $userProvider,
        private CommentVisibility $commentVisibility,
        private CommentOutputFactory $commentOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param array{id: numeric-string} $uriVariables
     * @param array<string, mixed>      $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CommentOutput
    {
        if ($operation instanceof Get === false) {
            throw new InvalidArgumentException('Only Get operation is supported');
        }

        $id = (int)Assert::numeric($uriVariables['id']);

        $comment = $this->commentRepository->find($id);
        $user    = $this->userProvider->getCurrentUser();
        if ($comment === null || $this->commentVisibility->isVisible($comment, $user) === false) {
            throw new NotFoundHttpException();
        }

        return $this->commentOutputFactory->create($comment);
    }
}
