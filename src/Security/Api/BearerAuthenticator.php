<?php
declare(strict_types=1);

namespace DR\Review\Security\Api;

use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

class BearerAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public function __construct(private readonly UserAccessTokenRepository $accessTokenRepository)
    {
    }

    /**
     * @inheritDoc
     */
    public function supports(Request $request): ?bool
    {
        if ($request->getPathInfo() === '/api/docs') {
            return false;
        }

        $path = $request->getPathInfo();

        // For /_mcp always run authentication so that onAuthenticationFailure can return 401 directly.
        if ($path === '/_mcp') {
            return true;
        }

        if (str_starts_with($path, '/api/') === false) {
            return false;
        }

        if ($request->headers->has('authorization') === false) {
            return false;
        }

        return str_starts_with((string)$request->headers->get('authorization'), 'Bearer ');
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $authorization = (string)$request->headers->get('authorization', '');
        if (str_starts_with($authorization, 'Bearer ') === false) {
            throw new AuthenticationException('Access denied');
        }

        $identifier = preg_replace('/^Bearer /', '', $authorization);

        $token = $this->accessTokenRepository->findOneBy(['token' => $identifier]);
        $user  = $token?->getUser();
        if ($token === null || $user === null) {
            throw new AuthenticationException('Access denied');
        }

        if (in_array(Roles::ROLE_USER, $user->getRoles(), true) === false) {
            throw new AuthenticationException('Access denied');
        }

        $token->setUsages($token->getUsages() + 1);
        $token->setUseTimestamp(time());
        $this->accessTokenRepository->save($token, true);

        return new SelfValidatingPassport(new UserBadge($user->getUserIdentifier(), static fn() => $user));
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        if ($request->getPathInfo() === '/_mcp') {
            return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function start(Request $request, ?AuthenticationException $authException = null): Response
    {
        return new JsonResponse(['error' => 'Authentication required'], Response::HTTP_UNAUTHORIZED);
    }
}
