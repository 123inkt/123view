<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Security\AzureAd;

use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class AzureAdAuthenticator extends AbstractAuthenticator
{
    public function __construct(
        private LoginService $loginService,
        private AzureAdUserBadgeFactory $userBadgeFactory,
        private UrlGeneratorInterface $urlGenerator
    ) {
    }

    /**
     * Called on every request to decide if this authenticator should be
     * used for the request. Returning `false` will cause this authenticator
     * to be skipped.
     */
    public function supports(Request $request): ?bool
    {
        return $request->getPathInfo() === '/single-sign-on/azure-ad/callback';
    }

    public function authenticate(Request $request): Passport
    {
        $result = $this->loginService->handleLogin($request);
        if ($result instanceof LoginFailure) {
            throw new AuthenticationException($result->getMessage());
        }

        return new SelfValidatingPassport($this->userBadgeFactory->create($result->getEmail(), $result->getName()));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // redirect to app dashboard
        $url = $this->urlGenerator->generate(RulesController::class);

        return new RedirectResponse($url);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // redirect to login page
        $url = $this->urlGenerator->generate(AuthenticationController::class, ['error_message' => $exception->getMessage()]);

        return new RedirectResponse($url);
    }
}
