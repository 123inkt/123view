<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Repository\Ai\AiReviewReferenceMatcherRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteAiReviewReferenceMatcherController extends AbstractController
{
    public function __construct(private readonly AiReviewReferenceMatcherRepository $matcherRepository)
    {
    }

    #[Route('/app/admin/ai-review/reference/{referenceId<\d+>}/matcher/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(int $referenceId, int $id): RedirectResponse
    {
        $matcher = $this->matcherRepository->find($id);
        if ($matcher === null || $matcher->getReference()->getId() !== $referenceId) {
            throw new NotFoundHttpException('Matcher not found');
        }

        $this->matcherRepository->remove($matcher, true);

        $this->addFlash('success', 'ai.review.reference.matcher.successful.removed');

        return $this->redirectToRoute(AiReviewReferenceController::class, ['id' => $referenceId]);
    }
}
