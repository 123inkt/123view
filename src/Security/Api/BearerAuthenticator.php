<?php
declare(strict_types=1);

namespace DR\Review\Security\Api;

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
        return new SelfValidatingPassport(new UserBadge('test'));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return new Response('');
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        return null;
    }
}
