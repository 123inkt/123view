<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class DeleteWebhookController extends AbstractController
{
    public function __construct(private WebhookRepository $webhookRepository)
    {
    }

    #[Route('/app/admin/webhook/{id<\d+>}', self::class, methods: ['DELETE'])]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(#[MapEntity] Webhook $webhook): RedirectResponse
    {
        $this->webhookRepository->remove($webhook, true);

        $this->addFlash('success', 'webhook.successful.removed');

        return $this->refererRedirect(WebhooksController::class);
    }
}
