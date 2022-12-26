<?php
declare(strict_types=1);

namespace DR\Review\Security\AzureAd;

use DR\Review\Controller\App\Review\ProjectsController;
use DR\Review\Controller\App\User\UserApprovalPendingController;
use DR\Review\Controller\Auth\LoginController;
use DR\Review\Security\Role\Roles;
use DR\Review\Utility\Assert;
use JsonException;
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
     * @throws JsonException
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        if (in_array(Roles::ROLE_USER, $token->getRoleNames(), true) === false) {
            return new RedirectResponse($this->urlGenerator->generate(UserApprovalPendingController::class));
        }

        $url = null;
        if ($request->query->has('state')) {
            $url = Assert::isArray(json_decode($request->query->get('state'), true, 512, JSON_THROW_ON_ERROR))['next'] ?? null;
        }
        $url ??= $this->urlGenerator->generate(ProjectsController::class);

        return new RedirectResponse($url);
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $request->getSession()->getFlashBag()->add('error', $exception->getMessage());

        // redirect to login page
        return new RedirectResponse($this->urlGenerator->generate(LoginController::class));
    }
}
