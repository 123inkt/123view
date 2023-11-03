<?php
declare(strict_types=1);

namespace DR\Review\Security\Webhook;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

class GitlabWebhookAuthenticator extends AbstractAuthenticator
{
    public function __construct(private readonly string $gitlabWebhookSecret)
    {
    }

    public function supports(Request $request): ?bool
    {
        if ($this->gitlabWebhookSecret === ''
            || $request->getPathInfo() !== '/webhook/gitlab'
            || $request->headers->has('X-Gitlab-Token') === false) {
            return false;
        }

        return $request->headers->get('X-Gitlab-Token') === $this->gitlabWebhookSecret;
    }

    /**
     * @inheritDoc
     */
    public function authenticate(Request $request): Passport
    {
        $user = new WebhookUser('gitlab-webhook', ['ROLE_GITLAB_WEBHOOK']);

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
