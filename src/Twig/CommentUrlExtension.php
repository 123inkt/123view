<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
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
        return [new TwigFunction('comment_url', [$this, 'getUrl'], ['is_safe' => ['all']])];
    }

    public function getUrl(Comment $comment, bool $absolute = false): string
    {
        return $this->urlGenerator->generate(
            ReviewController::class,
            [
                'review'   => $comment->getReview(),
                'filepath' => $comment->getFilePath()
            ],
            $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH
        );
    }
}
