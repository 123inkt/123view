<?php
declare(strict_types=1);

namespace DR\Review\Service\Api\Gitlab;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class GitlabCommentFormatter implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly string $appAbsoluteUrl, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function format(Comment $comment): string
    {
        $review        = $comment->getReview();
        $lineReference = $comment->getLineReference();
        $filePath      = $lineReference->newPath ?? $lineReference->oldPath ?? '';

        $url = $this->appAbsoluteUrl;
        $url .= $this->urlGenerator->generate(ReviewController::class, ['review' => $review, 'filePath' => $filePath]);

        // normalize @user annotation
        $message = preg_replace('/@user:\d+\[([^]]+)]/', '[$1]', $comment->getMessage());

        // add link to review
        return sprintf("%s\n<br>\n<br>\n[123view: CR-%d](%s#focus:comment:%d)", $message, $review->getProjectId(), $url, $comment->getId());
    }
}
