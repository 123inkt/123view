<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Ai\AiReviewReferenceMatcher;
use DR\Review\Form\Ai\EditAiReviewReferenceMatcherFormType;
use DR\Review\Repository\Ai\AiReviewReferenceMatcherRepository;
use DR\Review\Repository\Ai\AiReviewReferenceRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditAiReviewReferenceMatcherViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AiReviewReferenceMatcherController extends AbstractController
{
    public function __construct(
        private readonly AiReviewReferenceRepository $referenceRepository,
        private readonly AiReviewReferenceMatcherRepository $matcherRepository,
    ) {
    }

    /**
     * @return array<string, EditAiReviewReferenceMatcherViewModel>|RedirectResponse
     */
    #[Route('/app/admin/ai-review/reference/{referenceId<\d+>}/matcher/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/ai_review/edit_matcher.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, int $referenceId, ?int $id = null): array|RedirectResponse
    {
        $reference = $this->referenceRepository->find($referenceId);
        if ($reference === null) {
            throw new NotFoundHttpException('Reference not found');
        }

        $matcher = $id === null ? null : $this->matcherRepository->find($id);
        if ($id !== null && ($matcher === null || $matcher->getReference()->getId() !== $referenceId)) {
            throw new NotFoundHttpException('Matcher not found');
        }

        $matcher ??= new AiReviewReferenceMatcher();

        $form = $this->createForm(EditAiReviewReferenceMatcherFormType::class, ['reference' => $reference, 'matcher' => $matcher]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editAiReviewReferenceMatcherModel' => new EditAiReviewReferenceMatcherViewModel($reference, $matcher, $form->createView())];
        }

        if ($matcher->hasId() === false) {
            $reference->addMatcher($matcher);
        }
        $this->matcherRepository->save($matcher, true);

        $this->addFlash('success', 'ai.review.reference.matcher.successful.saved');

        return $this->redirectToRoute(AiReviewReferenceController::class, ['id' => $referenceId]);
    }
}
