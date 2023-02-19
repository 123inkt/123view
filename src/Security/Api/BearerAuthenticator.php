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

    /**
     * @inheritDoc
     */
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

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $identifier = preg_replace('/^Bearer /', '', (string)$request->headers->get('authorization'));

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
        return null;
    }
}
