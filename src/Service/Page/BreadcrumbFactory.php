<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Page;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Review\ReviewsController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Model\Page\Breadcrumb;
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
                ucfirst((string)$repository->getDisplayName()),
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
                ucfirst((string)$review->getRepository()?->getDisplayName()),
                $this->urlGenerator->generate(ReviewsController::class, ['id' => $review->getRepository()?->getId()])
            ),
            new Breadcrumb(
                'CR-' . $review->getProjectId(),
                $this->urlGenerator->generate(ReviewController::class, ['id' => $review->getId()])
            )
        ];
    }
}
