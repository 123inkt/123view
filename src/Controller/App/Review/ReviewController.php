<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ReviewController
{
    /**
     * @return array<string, ProjectsViewModel>
     */
    #[Route('app/reviews/{id<\d+>}', name: self::class, methods: 'GET')]
    #[Template('app/review/review.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(CodeReview $review): array
    {
        return ['reviewModel' => new ReviewViewModel($review)];
    }
}
