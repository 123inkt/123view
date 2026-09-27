<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\Operation;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @implements ProviderInterface<CommentReplyOutput>
 */
readonly class CommentReplyProvider implements ProviderInterface
{
    public function __construct(
        private CommentReplyRepository $commentReplyRepository,
        private UserEntityProvider $userProvider,
        private CommentVisibility $commentVisibility,
        private CommentReplyOutputFactory $commentReplyOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function provide(Operation $operation, array $uriVariables = [], array $context = []): CommentReplyOutput
    {
        Assert::isInstanceOf($operation, Get::class, 'Only Get operation is supported');
        $id = (int)Assert::numeric($uriVariables['id']);

        $reply = $this->commentReplyRepository->find($id);
        $user  = $this->userProvider->getCurrentUser();
        if ($reply === null || $this->commentVisibility->isVisible($reply->getComment(), $user) === false) {
            throw new NotFoundHttpException();
        }

        return $this->commentReplyOutputFactory->create($reply);
    }
}
