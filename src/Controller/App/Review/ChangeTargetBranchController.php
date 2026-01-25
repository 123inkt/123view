<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\ChangeReviewTargetBranchFormType;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class ChangeTargetBranchController extends AbstractController
{
    public function __construct(private readonly CodeReviewRepository $reviewRepository)
    {
    }

    #[Route('app/reviews/{id<\d+>}/target-branch', name: self::class, methods: 'POST')]
    #[IsGranted(Roles::ROLE_USER)]
    public function __invoke(Request $request, #[MapEntity] CodeReview $review): Response
    {
        $form = $this->createForm(ChangeReviewTargetBranchFormType::class, $review, ['review' => $review]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            throw new BadRequestHttpException('Invalid form submission');
        }

        $this->reviewRepository->save($review, true);

        return $this->redirectToRoute(ReviewController::class, ['review' => $review]);
    }
}
