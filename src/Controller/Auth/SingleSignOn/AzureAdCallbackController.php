<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth\SingleSignOn;

use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

class AzureAdCallbackController
{
    #[Route('/single-sign-on/azure-ad/callback', name: self::class, methods: 'GET')]
    public function __invoke(): RedirectResponse|JsonResponse
    {
        // never called, handled by AzureAdAuthenticator
        throw new RuntimeException('AzureAdAuthenticator route is not configured');
    }
}
