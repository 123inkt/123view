<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Ai\AiReviewReferenceSectionRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteAiReviewReferenceSectionController extends AbstractController
{
    public function __construct(private readonly AiReviewReferenceSectionRepository $sectionRepository)
    {
    }

    #[Route('/app/admin/ai-review/reference/{referenceId<\d+>}/section/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(int $referenceId, int $id): RedirectResponse
    {
        $section = $this->sectionRepository->find($id);
        if ($section === null || $section->getReference()->getId() !== $referenceId) {
            throw new NotFoundHttpException('Section not found');
        }

        $this->sectionRepository->remove($section, true);

        $this->addFlash('success', 'ai.review.reference.section.successful.removed');

        return $this->redirectToRoute(AiReviewReferenceController::class, ['id' => $referenceId]);
    }
}
