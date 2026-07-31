<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Ai\AiReviewReferenceSection;
use DR\Review\Form\Ai\EditAiReviewReferenceSectionFormType;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\Repository\Ai\AiReviewReferenceSectionRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditAiReviewReferenceSectionViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AiReviewReferenceSectionController extends AbstractController
{
    public function __construct(
        private readonly AiReviewReferenceRepository $referenceRepository,
        private readonly AiReviewReferenceSectionRepository $sectionRepository,
    ) {
    }

    /**
     * @return array<string, EditAiReviewReferenceSectionViewModel>|RedirectResponse
     */
    #[Route('/app/admin/ai-review/reference/{referenceId<\d+>}/section/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/ai_review/edit_section.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, int $referenceId, ?int $id = null): array|RedirectResponse
    {
        $reference = $this->referenceRepository->find($referenceId);
        if ($reference === null) {
            throw new NotFoundHttpException('Reference not found');
        }

        $section = $id === null ? null : $this->sectionRepository->find($id);
        if ($id !== null && ($section === null || $section->getReference()->getId() !== $referenceId)) {
            throw new NotFoundHttpException('Section not found');
        }

        $section ??= new AiReviewReferenceSection()->setSortOrder(0);

        $form = $this->createForm(EditAiReviewReferenceSectionFormType::class, ['reference' => $reference, 'section' => $section]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editAiReviewReferenceSectionModel' => new EditAiReviewReferenceSectionViewModel($reference, $section, $form->createView())];
        }

        if ($section->hasId() === false) {
            $reference->addSection($section);
        }
        $this->sectionRepository->save($section, true);

        $this->addFlash('success', 'ai.review.reference.section.successful.saved');

        return $this->redirectToRoute(AiReviewReferenceController::class, ['id' => $referenceId]);
    }
}
