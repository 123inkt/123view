<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller\Auth\SingleSignOn;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class AzureAdCallbackController
{
    public function __construct(private LoginService $loginService)
    {
    }

    #[Route('/single-sign-on/azure-ad/callback', name: self::class)]
    public function __invoke(Request $request): RedirectResponse
    {
        $result = $this->loginService->handleLogin($request);
        if ($result instanceof LoginFailure) {
            return ['success' => false, 'message' => $result->getMessage()];
        }

        return new RedirectResponse('https://www.google.com');
    }

    //private UserTokenRepository $tokenRepository;
    //private LoginService        $loginService;
    //
    //public function __construct(LoginService $loginService, UserTokenRepository $tokenRepository)
    //{
    //    $this->loginService    = $loginService;
    //    $this->tokenRepository = $tokenRepository;
    //}
    //
    ///**
    // * @Route(
    // *     "/single-sign-on/azure-ad/callback",
    // *     methods={"GET"},
    // *     name="single_sign_on_azure_ad_callback"
    // * )
    // * @Template("single-sign-on/result.html.twig")
    // * @throws DatabaseException|TokenException
    // */
    //public function __invoke(Request $request): RedirectResponse|array
    //{
    //    $result = $this->loginService->handleLogin($request);
    //    if ($result instanceof LoginFailure) {
    //        return ['success' => false, 'message' => $result->getMessage()];
    //    }
    //
    //    $user = $result->getUserInfo();
    //
    //    try {
    //        $state = json_decode($request->query->get('state', '[]'), true, 512, JSON_THROW_ON_ERROR);
    //    } catch (JsonException $e) {
    //        $state = [];
    //    }
    //
    //    // create token for user
    //    $token = $this->tokenRepository->createToken(
    //        (int)$user['userid'],
    //        RequestHelper::getIpLong($request),
    //        (string)($state['client-pcname'] ?? ''),
    //        (string)($state['client-mac'] ?? ''),
    //    );
    //
    //    return new RedirectResponse("drs://token/" . urlencode($token));
    //}
}
