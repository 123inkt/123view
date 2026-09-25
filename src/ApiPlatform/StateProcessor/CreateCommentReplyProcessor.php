<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\ApiPlatform\Input\CreateCommentReplyInput;
use DR\Review\ApiPlatform\Output\CommentReplyOutput;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @implements ProcessorInterface<CreateCommentReplyInput, CommentReplyOutput>
 */
class CreateCommentReplyProcessor implements ProcessorInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly CommentRepository $commentRepository,
        private readonly CommentReplyRepository $commentReplyRepository,
        private readonly UserEntityProvider $userProvider,
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
        Assert::isInstanceOf($data, CreateCommentReplyInput::class);
        Assert::isInstanceOf($operation, Post::class, 'Only Post operation is supported.');

        $comment = $this->commentRepository->find((int)Assert::numeric($uriVariables['commentId'] ?? null));
        if ($comment === null) {
            throw new NotFoundHttpException('Comment not found.');
        }

        if ($comment->getType() === CommentTypeEnum::Draft) {
            throw new BadRequestHttpException('Replies cannot be added to draft comments.');
        }

        $user      = $this->userProvider->getCurrentUser();
        $timestamp = $this->now()->getTimestamp();
        $reply     = new CommentReply();
        $reply->setComment($comment);
        $reply->setUser($user);
        $reply->setMessage(trim($data->message));
        $reply->setTag($data->tag);
        $reply->setCreateTimestamp($timestamp);
        $reply->setUpdateTimestamp($timestamp);

        $this->commentReplyRepository->save($reply, true);
        $this->bus->dispatch(new CommentReplyAdded(
            $comment->getReview()->getId(),
            $reply->getId(),
            $user->getId(),
            $reply->getMessage(),
            $comment->getFilePath(),
        ));

        return $this->commentReplyOutputFactory->create($reply);
    }
}
