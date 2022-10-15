<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Entity\Review\CodeReviewer;
use DR\GitCommitNotification\Form\Review\AddReviewerFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RemoveReviewerController extends AbstractController
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    #[Route('app/reviews/{reviewId<\d+>}/reviewer/{reviewerId<\d+>}', name: self::class, methods: 'DELETE')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review', expr: 'repository.find(reviewId)')]
    #[Entity('reviewer', expr: 'repository.find(reviewerId)')]
    public function __invoke(CodeReview $review, CodeReviewer $reviewer): RedirectResponse
    {
        $url = $this->generateUrl(ReviewController::class, ['id' => $review->getId()]);

        $em = $this->registry->getManager();
        $em->remove($reviewer);
        $em->flush();

        return $this->redirect($url);
    }
}
