<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use Doctrine\ORM\Tools\Pagination\Paginator;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Repository\Review\CodeReviewRepository;
use DR\GitCommitNotification\ViewModel\App\Review\ProjectsViewModel;
use DR\GitCommitNotification\ViewModel\App\Review\ReviewsViewModel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ReviewsController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    /**
     * @return array<string, ProjectsViewModel>
     */
    #[Route('app/projects/{id<\d+>}/reviews', name: self::class, methods: 'GET')]
    #[Template('app/review/reviews.html.twig')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('repository')]
    public function __invoke(Request $request, Repository $repository): array
    {
        $searchQuery = trim((string)$request->query->get('search'));
        $page        = $request->query->getInt('page', 1);

        $query = $this->reviewRepository->createQueryBuilder('r')
            ->where('r.repository = :repositoryId')
            ->setParameter('repositoryId', (int)$repository->getId())
            ->orderBy('r.id', 'DESC')
            ->setFirstResult(max(0, $page - 1) * 50)
            ->setMaxResults(50);

        if ($searchQuery !== '') {
            $query->andWhere('r.title LIKE :title')
                ->setParameter('title', '%' . addcslashes($searchQuery, "%_") . '%');
        }

        return ['reviewsModel' => new ReviewsViewModel(new Paginator($query, false), $page, $searchQuery)];
    }
}
