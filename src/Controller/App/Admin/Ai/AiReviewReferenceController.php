<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Form\Ai\EditAiReviewReferenceFormType;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditAiReviewReferenceViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AiReviewReferenceController extends AbstractController
{
    public function __construct(private readonly AiReviewReferenceRepository $referenceRepository)
    {
    }

    /**
     * @return array<string, EditAiReviewReferenceViewModel>|RedirectResponse
     */
    #[Route('/app/admin/ai-review/reference/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/ai_review/edit_reference.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, #[MapEntity] ?AiReviewReference $reference): array|RedirectResponse
    {
        if ($reference === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Reference not found');
        }

        $reference ??= new AiReviewReference()->setEnabled(true)->setPriority(0);

        $form = $this->createForm(EditAiReviewReferenceFormType::class, ['reference' => $reference]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editAiReviewReferenceModel' => new EditAiReviewReferenceViewModel($reference, $form->createView())];
        }

        $this->referenceRepository->save($reference, true);

        $this->addFlash('success', 'ai.review.reference.successful.saved');

        return $this->redirectToRoute(AiReviewReferenceController::class, ['id' => $reference->getId()]);
    }
}
