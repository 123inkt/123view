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
use Symfony\Component\DependencyInjection\Loader\Configurator\App;
use Symfony\Component\Security\Core\Authorization\Voter\AuthenticatedVoter;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

return App::config([
    'security' => [
        // https://symfony.com/doc/current/security.html#registering-the-user-hashing-passwords
        'password_hashers' => [PasswordAuthenticatedUserInterface::class => ['algorithm' => 'auto']],
        'providers'        => ['app_user_provider' => ['entity' => ['class' => User::class, 'property' => 'email']]],
        'firewalls'        => [
            'dev'      => ['pattern' => '^/(_(profiler|wdt)|css|images|js)/', 'security' => false],
            'api_docs' => ['pattern' => '^/api/docs', 'stateless' => true, 'security' => false],
            'api'      => ['pattern' => '^/api', 'stateless' => true, 'custom_authenticators' => [BearerAuthenticator::class]],
            'main'     => [
                'lazy'                  => true,
                'provider'              => 'app_user_provider',
                'user_checker'          => UserChecker::class,
                'custom_authenticators' => [AzureAdAuthenticator::class],
                // setup login flow
                'form_login'            => [
                    'login_path'          => LoginController::class,
                    'check_path'          => LoginController::class,
                    'enable_csrf'         => true,
                    'default_target_path' => ProjectsController::class,
                ],
                // setup logout flow
                'logout'                => ['path' => LogoutController::class, 'target' => LoginController::class],
            ],
        ],
        'access_control'   => [
            ['path' => '^/app/assets/', 'roles' => AuthenticatedVoter::PUBLIC_ACCESS],
            ['path' => '^/app', 'roles' => AuthenticatedVoter::IS_AUTHENTICATED_FULLY],
            ['path' => '^/log-viewer', 'roles' => [Roles::ROLE_ADMIN]],
        ],
    ],
]);
