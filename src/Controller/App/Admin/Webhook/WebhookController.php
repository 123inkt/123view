<?php
declare(strict_types=1);

namespace DR\Review\Controller\App\Admin\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Form\Webhook\EditWebhookFormType;
use DR\Review\Repository\Webhook\WebhookRepository;
use DR\Review\Security\Role\Roles;
use DR\Review\ViewModel\App\Admin\EditWebhookViewModel;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class WebhookController extends AbstractController
{
    public function __construct(private WebhookRepository $webhookRepository)
    {
    }

    /**
     * @return array<string, EditWebhookViewModel>|RedirectResponse
     */
    #[Route('/app/admin/webhook/{id<\d+>?}', self::class, methods: ['GET', 'POST'])]
    #[Template('app/admin/edit_webhook.html.twig')]
    #[IsGranted(Roles::ROLE_ADMIN)]
    public function __invoke(Request $request, #[MapEntity] ?Webhook $webhook): array|RedirectResponse
    {
        if ($webhook === null && $request->attributes->get('id') !== null) {
            throw new NotFoundHttpException('Webhook not found');
        }

        $webhook ??= (new Webhook())->setEnabled(true)->setRetries(3)->setVerifySsl(true);

        $form = $this->createForm(EditWebhookFormType::class, ['webhook' => $webhook]);
        $form->handleRequest($request);
        if ($form->isSubmitted() === false || $form->isValid() === false) {
            return ['editWebhookModel' => new EditWebhookViewModel($webhook, $form->createView())];
        }

        $this->webhookRepository->save($webhook, true);

        $this->addFlash('success', 'webhook.successful.saved');

        return $this->redirectToRoute(WebhooksController::class);
    }
}
