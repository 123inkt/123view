<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Repository\Review\CommentReplyRepository;
use DR\Utils\Assert;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CodeReviewActivityUrlGenerator implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator, private readonly CommentReplyRepository $replyRepository)
    {
    }

    public function generate(CodeReviewActivity $activity): string
    {
        $review = Assert::notNull($activity->getReview());
        $anchor = '';
        $params = [];

        switch ($activity->getEventName()) {
            case CommentAdded::NAME:
            case CommentUpdated::NAME:
            $anchor = '#comment-' . $activity->getDataValue('commentId');
                $params['filePath'] = (string)$activity->getDataValue('file');
                break;
            case CommentReplyAdded::NAME:
            case CommentReplyUpdated::NAME:
                $comment = $this->replyRepository->find($activity->getDataValue('commentId'))?->getComment();
                if ($comment !== null) {
                    $anchor = '#reply-' . $activity->getDataValue('commentId');
                    $params['filePath'] = $comment->getFilePath();
                }
                break;
        }

        return $this->urlGenerator->generate(ReviewController::class, ['review' => $review] + $params) . $anchor;
    }
}
