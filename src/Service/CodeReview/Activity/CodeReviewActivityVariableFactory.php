<?php
declare(strict_types=1);

namespace DR\Review\Service\CodeReview\Activity;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Review\ActivityVariable;
use DR\Utils\Assert;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CodeReviewActivityVariableFactory
{
    public function __construct(private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    public function createFromComment(Comment $comment): ActivityVariable
    {
        $review   = Assert::notNull($comment->getReview());
        $filePath = $comment->getFilePath();
        $url      = $this->urlGenerator->generate(ReviewController::class, ['review' => $review, 'filePath' => $filePath]);
        $link     = sprintf(
            '<a href="%s#comment-%d">%s</a>',
            htmlspecialchars($url, ENT_QUOTES),
            (int)$comment->getId(),
            htmlspecialchars(basename($filePath), ENT_QUOTES)
        );

        return new ActivityVariable('file', $link, true);
    }

    /**
     * @param ActivityVariable[] $variables
     *
     * @return string[]
     */
    public function createParams(array $variables): array
    {
        $params = [];
        foreach ($variables as $variable) {
            $params[$variable->key] = $variable->htmlSafe ? $variable->value : htmlspecialchars($variable->value, ENT_QUOTES);
        }

        return $params;
    }
}
