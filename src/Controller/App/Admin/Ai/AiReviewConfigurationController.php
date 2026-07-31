<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Ai;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Ai\AiReviewConfiguration;
use DR\Review\Form\Ai\EditAiReviewConfigurationFormType;
use DR\Review\Repository\Ai\AiReviewConfigurationRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditAiReviewConfigurationViewModel;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class AiReviewConfigurationController extends AbstractController
{
    public function __construct(private readonly AiReviewConfigurationRepository $configurationRepository)
    {
    }

    /**
     * @return array<string, EditAiReviewConfigurationViewModel>|RedirectResponse
     */
    #[Route('/app/admin/ai-review/instructions', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/ai_review/edit_configuration.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request): array|RedirectResponse
    {
        $configuration = $this->configurationRepository->find(AiReviewConfiguration::SINGLETON_ID) ?? new AiReviewConfiguration();

        $form = $this->createForm(EditAiReviewConfigurationFormType::class, ['configuration' => $configuration]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editAiReviewConfigurationModel' => new EditAiReviewConfigurationViewModel($configuration, $form->createView())];
        }

        $configuration->setUpdateTimestamp(time());
        $this->configurationRepository->save($configuration, true);

        $this->addFlash('success', 'ai.review.instructions.successful.saved');

        return $this->redirectToRoute(self::class);
    }
}
