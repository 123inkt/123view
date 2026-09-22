<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Operation;
use ApiPlatform\Metadata\Post;
use ApiPlatform\State\ProcessorInterface;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Input\CreateCommentInput;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\NotificationStatus;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\Comment\CommentLocationResolver;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * @implements ProcessorInterface<CreateCommentInput, CommentOutput>
 * @SuppressWarnings(CouplingBetweenObjects)
 */
class CreateCommentProcessor implements ProcessorInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewRevisionService $reviewRevisionService,
        private readonly CommentLocationResolver $locationResolver,
        private readonly LineReferenceFactory $lineReferenceFactory,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentOutputFactory $commentOutputFactory,
    ) {
    }

    /**
     * @param array<string, mixed> $uriVariables
     * @param array<string, mixed> $context
     *
     * @SuppressWarnings(UnusedFormalParameter)
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentOutput
    {
        unset($context);
        Assert::isInstanceOf($data, CreateCommentInput::class);
        Assert::isInstanceOf($operation, Post::class, 'Only Post operation is supported.');

        $reviewId = $this->getReviewId($uriVariables['reviewId'] ?? null);
        $review   = $this->reviewRepository->find($reviewId);
        if ($review === null) {
            throw new NotFoundHttpException('Code review not found.');
        }

        $user = $this->userProvider->getCurrentUser();
        $revisions = $this->reviewRevisionService->getRevisions($review);
        /** @var Revision|null $revision */
        $revision = Arrays::lastOrNull($revisions);
        if ($revision === null) {
            throw new UnprocessableEntityHttpException('The review has no resolvable revision.');
        }

        $message  = trim(Assert::string($data->message));
        $filepath = trim(Assert::string($data->filepath));
        $line     = Assert::positiveInt(Assert::integer($data->line));
        $this->locationResolver->resolve($review, $filepath, $line);

        $tag = $this->getTag($data->tag);
        $timestamp = $this->now()->getTimestamp();

        $comment = new Comment();
        $comment->setReview($review);
        $comment->setUser($user);
        $comment->setMessage($message);
        $comment->setFilePath($filepath);
        $comment->setLineReference($this->lineReferenceFactory->createFromReview($review, $filepath, $line, $revision->getCommitHash()));
        $comment->setTag($tag);
        $comment->setType(CommentTypeEnum::Final);
        $comment->setState(CommentStateEnum::Open);
        $comment->setNotificationStatus(NotificationStatus::all());
        $comment->setCreateTimestamp($timestamp);
        $comment->setUpdateTimestamp($timestamp);

        $review->getComments()->add($comment);
        $this->commentRepository->save($comment, true);

        return $this->commentOutputFactory->create($comment);
    }

    private function getReviewId(mixed $value): int
    {
        if ((is_int($value) === false && is_string($value) === false) || ctype_digit((string)$value) === false || (int)$value < 1) {
            throw new UnprocessableEntityHttpException('The review ID must be a positive integer.');
        }

        return (int)$value;
    }

    private function getTag(?string $value): ?CommentTagEnum
    {
        if ($value === null) {
            return null;
        }

        $tag = CommentTagEnum::tryFrom($value);
        if ($tag === null) {
            throw new UnprocessableEntityHttpException('The comment tag is invalid.');
        }

        return $tag;
    }
}
