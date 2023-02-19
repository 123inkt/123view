<?php
declare(strict_types=1);

namespace DR\Review\Security\Api;

use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Role\Roles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class BearerAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly UserAccessTokenRepository $accessTokenRepository)
    {
    }

    public function supports(Request $request): ?bool
    {
        if ($request->getPathInfo() === '/api/docs') {
            return false;
        }

        if (str_starts_with($request->getPathInfo(), '/api/') === false) {
            return false;
        }

        if ($request->headers->has('authorization') === false) {
            return false;
        }

        return str_starts_with((string)$request->headers->get('authorization'), 'Bearer ');
    }

    public function authenticate(Request $request): Passport
    {
        $identifier = preg_replace('/^Bearer /', '', (string)$request->headers->get('authorization'));

        $user = $this->accessTokenRepository->findOneBy(['token' => $identifier])?->getUser();
        if ($user === null) {
            throw new AuthenticationException('Access denied');
        }

        if (in_array(Roles::ROLE_USER, $user->getRoles(), true) === false) {
            throw new AuthenticationException('Access denied');
        }

        return new SelfValidatingPassport(
            new UserBadge(
                $user->getUserIdentifier(),
                static fn() => $user->addRole(Roles::ROLE_API)
            )
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
