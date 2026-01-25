<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Attribute\AsTwigFunction;

class CommentUrlExtension
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    #[AsTwigFunction(name: 'comment_url', isSafe: ['all'])]
    public function getCommentUrl(Comment $comment, bool $absolute = false): string
    {
        return $this->urlGenerator->generate(
            ReviewController::class,
            [
                'review'   => $comment->getReview(),
                'filePath' => $comment->getFilePath()
            ],
            $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }

    #[AsTwigFunction(name: 'comment_reply_url', isSafe: ['all'])]
    public function getCommentReplyUrl(CommentReply $reply, bool $absolute = false): string
    {
        $comment = $reply->getComment();

        return $this->urlGenerator->generate(
            ReviewController::class,
            [
                'review'   => $comment->getReview(),
                'filePath' => $comment->getFilePath()
            ],
            $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}
