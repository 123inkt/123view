<?php

declare(strict_types=1);

use DR\Review\Controller\App\Project\ProjectsController;
use DR\Review\Controller\Auth\LoginController;
use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Entity\User\User;
use DR\Review\Security\Api\BearerAuthenticator;
use DR\Review\Security\AzureAd\AzureAdAuthenticator;
use DR\Review\Security\Role\Roles;
use DR\Review\Security\UserChecker;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
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

    $security->firewall('login')
        ->pattern('^/api/login')
        ->stateless(true)
        ->jsonLogin()
            ->checkPath('/api/login')
            ->usernamePath('username')
            ->passwordPath('password')
            ->successHandler('lexik_jwt_authentication.handler.authentication_success')
            ->failureHandler('lexik_jwt_authentication.handler.authentication_failure');

    $security->firewall('api')
        ->pattern('^/api/view-model')
        ->stateless(true)
        ->customAuthenticators([JWTAuthenticator::class]);

    // TODO ANGULAR Fix
    //$security->firewall('api')
    //    ->pattern('^/api')
    //    ->stateless(true)
    //    ->customAuthenticators([BearerAuthenticator::class]);

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
        ->enableCsrf(false)
        ->defaultTargetPath(ProjectsController::class);

    // setup logout flow
    $security->firewall('main')
        ->logout()
        ->path(LogoutController::class)
        ->target(LoginController::class);

    $security->accessControl()->path('^/app')->roles(['IS_AUTHENTICATED_FULLY']);
    $security->accessControl()->path('^/api/view-model/login')->roles(['PUBLIC_ACCESS']);
    $security->accessControl()->path('^/api')->roles(['IS_AUTHENTICATED_FULLY']);
    $security->accessControl()->path('^/log-viewer')->roles([Roles::ROLE_ADMIN]);
};
