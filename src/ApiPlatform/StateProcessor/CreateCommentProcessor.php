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
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\Comment\CommentLocationValidator;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Symfony\Component\Clock\ClockAwareTrait;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Throwable;

/**
 * @implements ProcessorInterface<CreateCommentInput, CommentOutput>
 */
class CreateCommentProcessor implements ProcessorInterface
{
    use ClockAwareTrait;

    public function __construct(
        private readonly CodeReviewRepository $reviewRepository,
        private readonly CommentRepository $commentRepository,
        private readonly CodeReviewRevisionService $reviewRevisionService,
        private readonly CommentLocationValidator $locationResolver,
        private readonly LineReferenceFactory $lineReferenceFactory,
        private readonly UserEntityProvider $userProvider,
        private readonly CommentOutputFactory $commentOutputFactory,
    ) {
    }

    /**
     * @inheritDoc
     *
     * @param array{reviewId: numeric-string} $uriVariables
     * @param array<string, mixed>            $context
     *
     * @throws Throwable
     */
    public function process(mixed $data, Operation $operation, array $uriVariables = [], array $context = []): CommentOutput
    {
        Assert::isInstanceOf($data, CreateCommentInput::class);
        Assert::isInstanceOf($operation, Post::class, 'Only Post operation is supported.');

        $review = $this->reviewRepository->find((int)Assert::numeric($uriVariables['reviewId']));
        if ($review === null) {
            throw new NotFoundHttpException('Code review not found.');
        }

        $revision = Arrays::lastOrNull($this->reviewRevisionService->getRevisions($review));
        if ($revision === null) {
            throw new UnprocessableEntityHttpException('The review has no resolvable revision.');
        }

        $user     = $this->userProvider->getCurrentUser();
        $message  = trim($data->message);
        $filepath = trim($data->filepath);

        // validate location is valid
        $this->locationResolver->validate($review, $filepath, $data->line);

        $comment = new Comment();
        $comment->setReview($review);
        $comment->setUser($user);
        $comment->setMessage($message);
        $comment->setFilePath($filepath);
        $comment->setLineReference($this->lineReferenceFactory->createFromReview($review, $filepath, $data->line, $revision->getCommitHash()));
        $comment->setTag($data->tag);
        $comment->setType(CommentTypeEnum::Final);
        $comment->setState(CommentStateEnum::Open);
        $comment->setCreateTimestamp($this->now()->getTimestamp());
        $comment->setUpdateTimestamp($this->now()->getTimestamp());

        $review->getComments()->add($comment);
        $this->commentRepository->save($comment, true);

        return $this->commentOutputFactory->create($comment);
    }
}
