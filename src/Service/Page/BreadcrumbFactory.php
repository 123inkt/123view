<?php
declare(strict_types=1);

namespace DR\Review\Service\Page;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Controller\App\Reviews\ReviewsController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Model\Page\Breadcrumb;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class BreadcrumbFactory
{
    public function __construct(private UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @return Breadcrumb[]
     */
    public function createForReviews(Repository $repository): array
    {
        return [
            new Breadcrumb(
                ucfirst($repository->getDisplayName()),
                $this->urlGenerator->generate(ReviewsController::class, ['id' => $repository->getId()])
            )
        ];
    }

    /**
     * @return Breadcrumb[]
     */
    public function createForReview(CodeReview $review): array
    {
        return [
            new Breadcrumb(
                ucfirst($review->getRepository()->getDisplayName()),
                $this->urlGenerator->generate(ReviewsController::class, ['id' => $review->getRepository()->getId()])
            ),
            new Breadcrumb(
                'CR-' . $review->getProjectId(),
                $this->urlGenerator->generate(ReviewController::class, ['review' => $review])
            )
        ];
    }
}
