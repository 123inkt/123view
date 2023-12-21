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

    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function format(Comment $comment): string
    {
        $url = $this->urlGenerator->generate(ReviewController::class, ['review' => $comment->getReview()], UrlGeneratorInterface::ABSOLUTE_URL);

        $message = str_replace("\n", "\n<br>", $comment->getMessage());

        return sprintf("%s\n<br>\n<br>*%s*", $message, $url);
    }
}
