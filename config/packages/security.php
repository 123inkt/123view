<?php

declare(strict_types=1);

use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\Controller\Auth\LogoutController;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Config\SecurityConfig;

return static function (ContainerConfigurator $containerConfigurator, SecurityConfig $security): void {
    $security->enableAuthenticatorManager(true);
    $security->passwordHasher(PasswordAuthenticatedUserInterface::class)->algorithm('auto');

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
