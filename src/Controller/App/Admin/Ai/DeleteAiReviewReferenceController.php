<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Ai\AiReviewReference;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteAiReviewReferenceController extends AbstractController
{
    public function __construct(private readonly AiReviewReferenceRepository $referenceRepository)
    {
    }

    #[Route('/app/admin/ai-review/reference/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(#[MapEntity] AiReviewReference $reference): RedirectResponse
    {
        $this->referenceRepository->remove($reference, true);

        $this->addFlash('success', 'ai.review.reference.successful.removed');

        return $this->refererRedirect(AiReviewReferencesController::class);
    }
}
