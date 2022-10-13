<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class ReviewsController extends AbstractController
{
    /**
     * @return array<string, ProjectsViewModel>
     */
    #[Route('app/projects/{id<\d+>}/reviews', name: self::class, methods: 'GET')]
    #[Template('app/review/reviews.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('repository')]
    public function __invoke(Repository $repository): array
    {
        return [];
    }
}
