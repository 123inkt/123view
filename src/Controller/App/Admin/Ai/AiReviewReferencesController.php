<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\AiReviewReferencesViewModel;
use DR\Review\ViewModelProvider\AiReviewReferencesViewModelProvider;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AiReviewReferencesController extends AbstractController
{
    public function __construct(private readonly AiReviewReferencesViewModelProvider $viewModelProvider)
    {
    }

    /**
     * @return array<string, AiReviewReferencesViewModel>
     */
    #[Route('/app/admin/ai-review/references', self::class, methods: 'GET')]
    #[Template('app/admin/ai_review/references.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(): array
    {
        return ['aiReviewReferencesViewModel' => $this->viewModelProvider->getAiReviewReferencesViewModel()];
    }
}
