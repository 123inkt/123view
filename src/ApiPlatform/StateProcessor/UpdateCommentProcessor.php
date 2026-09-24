<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Input\UpdateCommentInput;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<UpdateCommentInput, CommentOutput>
 */
class UpdateCommentProcessor implements ProcessorInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentVisibility $commentVisibility,
        private readonly CommentOutputFactory $commentOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     * Conditions:
     * - Comment must exist
     * - Only author may change message and tag
     * - Only author may modify draft messages
     * - If comment is draft, state may not change
     * @param array{id: numeric-string} $uriVariables
     * @param array<string, mixed>      $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentOutput
    {
        Assert::isInstanceOf($data, UpdateCommentInput::class);
        Assert::isInstanceOf($operation, Patch::class, 'Only Patch operation is supported.');

        $comment = $this->commentRepository->find((int)Assert::numeric($uriVariables['id']));
        if ($comment === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        $user = $this->userProvider->getCurrentUser();
        if ($this->commentVisibility->isVisible($comment, $user) === false) {
            throw new NotFoundHttpException('Comment not found.');
        }

        $isAuthor = $comment->getUser()->getId() === $user->getId();
        if (($data->hasMessage() || $data->hasTag()) && $isAuthor === false) {
            throw new AccessDeniedHttpException('Only the comment author may update its message or tag.');
        }

        if ($data->hasState() && $comment->getType() === CommentTypeEnum::Draft) {
            throw new UnprocessableEntityHttpException('Draft comment state cannot be changed.');
        }

        if ($data->hasMessage()) {
            $comment->setMessage(trim($data->message));
        }
        if ($data->hasTag()) {
            $comment->setTag($data->getTag());
        }
        if ($data->hasState()) {
            $comment->setState($data->state);
        }
        $comment->setUpdateTimestamp($this->now()->getTimestamp());
        $this->commentRepository->save($comment, true);

        return $this->commentOutputFactory->create($comment);
    }
}
