<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Patch;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Input\UpdateCommentReplyInput;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Security\Voter\CommentReplyVoter;
use DR\Review\Service\CodeReview\Comment\CommentVisibility;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * @implements ProcessorInterface<UpdateCommentReplyInput, CommentReplyOutput>
 */
class UpdateCommentReplyProcessor implements ProcessorInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly CommentReplyRepository $commentReplyRepository,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentVisibility $commentVisibility,
        private readonly AuthorizationCheckerInterface $authorizationChecker,
        private readonly MessageBusInterface $bus,
        private readonly CommentReplyOutputFactory $commentReplyOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentReplyOutput
    {
        Assert::isInstanceOf($data, UpdateCommentReplyInput::class);
        Assert::isInstanceOf($operation, Patch::class, 'Only Patch operation is supported.');

        $reply = $this->commentReplyRepository->find((int)Assert::numeric($uriVariables['id']));
        if ($reply === null) {
            throw new NotFoundHttpException('Comment reply not found.');
        }

        $user = $this->userProvider->getCurrentUser();
        if ($this->commentVisibility->isVisible($reply->getComment(), $user) === false) {
            throw new NotFoundHttpException('Comment reply not found.');
        }

        if ($this->authorizationChecker->isGranted(CommentReplyVoter::EDIT, $reply) === false) {
            throw new AccessDeniedHttpException('Only the comment reply author may update its message or tag.');
        }

        $originalMessage = $reply->getMessage();
        if ($data->hasMessage()) {
            $reply->setMessage(trim($data->message));
        }
        if ($data->hasTag()) {
            $reply->setTag($data->getTag());
        }
        $reply->setUpdateTimestamp($this->now()->getTimestamp());
        $this->commentReplyRepository->save($reply, true);

        if ($reply->getMessage() !== $originalMessage) {
            $this->bus->dispatch(new CommentReplyUpdated(
                $reply->getComment()->getReview()->getId(),
                $reply->getId(),
                $user->getId(),
                $originalMessage,
            ));
        }

        return $this->commentReplyOutputFactory->create($reply);
    }
}
