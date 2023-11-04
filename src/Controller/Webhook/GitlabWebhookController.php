<?php
declare(strict_types=1);

namespace DR\Review\Controller\Webhook;

use DR\Review\Controller\AbstractController;
use DR\Review\Service\Webhook\Receive\Gitlab\WebhookRequestDeserializer;
use DR\Review\Service\Webhook\Receive\WebhookEventHandler;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GitlabWebhookController extends AbstractController
{
    public function __construct(private readonly WebhookRequestDeserializer $deserializer, private readonly WebhookEventHandler $eventHandler)
    {
    }

    #[Route('/webhook/gitlab', name: self::class, methods: 'POST')]
    #[IsGranted('ROLE_GITLAB_WEBHOOK')]
    public function __invoke(Request $request): Response
    {
        $eventType = $request->headers->get('X-Gitlab-Event', '');
        $data      = $request->getContent();

        $event = $this->deserializer->deserialize($eventType, $data);
        if ($event !== null) {
            $this->eventHandler->handle($event);
        }

        return new Response('OK');
    }
}
