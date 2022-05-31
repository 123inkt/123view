<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth\SingleSignOn;

use DR\GitCommitNotification\Security\AzureAd\LoginFailure;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AzureAdCallbackController
{
    public function __construct(private LoginService $loginService)
    {
    }

    #[Route('/single-sign-on/azure-ad/callback', name: self::class)]
    public function __invoke(Request $request): RedirectResponse|JsonResponse
    {
        $result = $this->loginService->handleLogin($request);
        if ($result instanceof LoginFailure) {
            return new JsonResponse(['success' => false, 'message' => $result->getMessage()]);
        }

        return new RedirectResponse('https://www.google.com');
    }
}
