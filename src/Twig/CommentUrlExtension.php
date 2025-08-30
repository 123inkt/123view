<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class CommentUrlExtension extends AbstractExtension
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('comment_url', [$this, 'getCommentUrl'], ['is_safe' => ['all']]),
            new TwigFunction('comment_reply_url', [$this, 'getCommentReplyUrl'], ['is_safe' => ['all']])
        ];
    }

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
