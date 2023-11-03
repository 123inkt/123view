<?php

declare(strict_types=1);

use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\Auth\LoginController;
use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Entity\User\User;
use DR\Review\Security\Api\BearerAuthenticator;
use DR\Review\Security\AzureAd\AzureAdAuthenticator;
use DR\Review\Security\UserChecker;
use DR\Review\Security\Webhook\GitlabWebhookAuthenticator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    // https://symfony.com/doc/current/security.html#registering-the-user-hashing-passwords
    $security->passwordHasher(PasswordAuthenticatedUserInterface::class)->algorithm('auto');

    $security->provider('app_user_provider')
        ->entity()
        ->class(User::class)
        ->property('email');

    $security->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security->firewall('api_docs')
        ->pattern('^/api/docs')
        ->stateless(true)
        ->security(false);

    $security->firewall('api')
        ->pattern('^/api')
        ->stateless(true)
        ->customAuthenticators([BearerAuthenticator::class]);

    $security->firewall('webhook')
        ->pattern('^/webhook/gitlab')
        ->stateless(true)
        ->customAuthenticators([GitlabWebhookAuthenticator::class]);

    $security->firewall('main')
        ->lazy(true)
        ->provider('app_user_provider')
        ->userChecker(UserChecker::class)
        ->customAuthenticators([AzureAdAuthenticator::class]);

    // setup login flow
    $security->firewall('main')
        ->formLogin()
        ->loginPath(LoginController::class)
        ->checkPath(LoginController::class)
        ->enableCsrf(true)
        ->defaultTargetPath(ProjectsController::class);

    // setup logout flow
    $security->firewall('main')
        ->logout()
        ->path(LogoutController::class)
        ->target(LoginController::class);

    // require IS_AUTHENTICATED_FULLY for /app/*
    $security->accessControl()
        ->path('^/app')
        ->roles(['IS_AUTHENTICATED_FULLY']);
};
