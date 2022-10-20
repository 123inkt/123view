<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\App\Review;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Form\Review\AddCommentFormType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AddCommentController extends AbstractController
{
    public function __construct(private ManagerRegistry $registry)
    {
    }

    #[Route('app/reviews/{id<\d+>}/add-comment', name: self::class, methods: 'POST')]
    #[IsGranted('IS_AUTHENTICATED_FULLY')]
    #[Entity('review')]
    public function __invoke(Request $request, CodeReview $review): void
    {
        $form = $this->createForm(AddCommentFormType::class, null, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            $this->refererRedirect(ReviewController::class, ['id' => $review->getId()]);
        }

        /** @var array<string, string> $data */
        $data    = $form->getData();
        $comment = $data['comment'];
    }
}
