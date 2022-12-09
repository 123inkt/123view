<?php

declare(strict_types=1);

use DR\Review\Controller\Auth\AuthenticationController;
use DR\Review\Controller\Auth\LogoutController;
use DR\Review\Entity\User\User;
use DR\Review\Security\AzureAd\AzureAdAuthenticator;
use DR\Review\Security\UserChecker;
use Symfony\Config\SecurityConfig;

return static function (SecurityConfig $security): void {
    $security->provider('app_user_provider')
        ->entity()
        ->class(User::class)
        ->property('email');

    $security->firewall('dev')
        ->pattern('^/(_(profiler|wdt)|css|images|js)/')
        ->security(false);

    $security->firewall('main')
        ->lazy(true)
        ->provider('app_user_provider')
        ->userChecker(UserChecker::class)
        ->customAuthenticators([AzureAdAuthenticator::class]);

    $security->firewall('main')
        ->logout()
        ->path(LogoutController::class)
        ->target(AuthenticationController::class);

    // require IS_AUTHENTICATED_FULLY for /app/*
    $security->accessControl()
        ->path('^/app')
        ->roles(['IS_AUTHENTICATED_FULLY']);
};
