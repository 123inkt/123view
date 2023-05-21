<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditWebhookViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteWebhookController extends AbstractController
{
    public function __construct(private WebhookRepository $webhookRepository)
    {
    }

    /**
     * @return array<string, EditWebhookViewModel>|RedirectResponse
     */
    #[Route('/app/admin/webhook/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(#[MapEntity] Webhook $webhook): array|RedirectResponse
    {
        $this->webhookRepository->remove($webhook, true);

        $this->addFlash('success', 'webhook.successful.removed');

        return $this->refererRedirect(WebhooksController::class);
    }
}
