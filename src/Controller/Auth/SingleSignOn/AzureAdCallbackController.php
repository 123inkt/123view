<?php
declare(strict_types=1);

namespace DR\Review\Controller\Auth\SingleSignOn;

use RuntimeException;
use Symfony\Component\Routing\Attribute\Route;

class AzureAdCallbackController
{
    #[Route('/single-sign-on/azure-ad/callback', name: self::class, methods: 'GET', condition: 'env("bool:APP_AUTH_AZURE_AD")')]
    public function __invoke(): void
    {
        // never called, handled by AzureAdAuthenticator
        throw new RuntimeException('AzureAdAuthenticator route is not configured');
    }
}
