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
        $url = $this->appAbsoluteUrl . $this->urlGenerator->generate(ReviewController::class, ['review' => $comment->getReview()]);

        $message = str_replace("\n", "\n<br>", $comment->getMessage());

        return sprintf("%s\n<br>\n<br>*123view: %s*", $message, $url);
    }
}
