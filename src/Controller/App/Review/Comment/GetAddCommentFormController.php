<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Form\Review\AddCommentFormType;
use DR\Review\Request\Comment\AddCommentRequest;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Form\FormView;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GetAddCommentFormController extends AbstractController
{
    /**
     * @return array<string, FormView|int[]>
     */
    #[Route('app/reviews/{id<\d+>}/comment', name: self::class, methods: 'GET')]
    #[IsGranted(Roles::ROLE_USER)]
    #[Template('app/review/comment/comment.modify.html.twig')]
    public function __invoke(AddCommentRequest $request, #[MapEntity] CodeReview $review): array
    {
        $form = $this->createForm(AddCommentFormType::class, null, ['review' => $review, 'lineReference' => $request->getLineReference()]);

        return ['form' => $form->createView(), 'actors' => $review->getActors()];
    }
}
