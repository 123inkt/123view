<?php
declare(strict_types=1);

namespace DR\Review\Controller\Webhook;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class GitlabWebhookController
{
    #[Route('/webhook/gitlab', name: self::class, methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_GITLAB_WEBHOOK')]
    public function __invoke(): Response
    {
        return new Response('OK');
    }
}
