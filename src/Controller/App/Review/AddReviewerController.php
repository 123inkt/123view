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

class AddReviewerController extends AbstractController
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    #[Route('app/reviews/{id<\d+>}/reviewer', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): RedirectResponse
    {
        $url  = $this->generateUrl(ReviewController::class, ['id' => $review->getId()]);
        $form = $this->createForm(AddReviewerFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isValid() === false) {
            return $this->redirect($url);
        }

        $user = $form->getData()['user'] ?? null;
        if ($user instanceof User === false) {
            return $this->redirect($url);
        }

        $reviewer = new CodeReviewer();
        $reviewer->setUser($user);
        $reviewer->setReview($review);
        $review->getReviewers()->add($reviewer);

        $em = $this->registry->getManager();
        //$em->persist($reviewer);
        $em->persist($review);
        $em->flush();

        return $this->redirect($url);
    }
}
