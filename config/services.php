<?php

declare(strict_types=1);

use CzProject\GitPhp\Git;
use CzProject\GitPhp\Runners\CliRunner;
use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\GitCommitNotification\Entity\Config\User;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Security\Core\Security;
use TheNetworg\OAuth2\Client\Provider\Azure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$allowCustomRecipients', '%env(bool:ALLOW_CUSTOM_RECIPIENTS_PER_RULE)%')
        ->bind('$upsourceApiUrl', '%env(UPSOURCE_API_URL)%')
        ->bind('$gitlabApiUrl', '%env(GITLAB_API_URL)%');

    // Register controllers
    $services->load('DR\GitCommitNotification\Controller\\', '../src/Controller/**/*Controller.php')->tag('controller.service_arguments');

    // auto-wire commands, services and twig-extensions
    $services->load('DR\GitCommitNotification\Command\\', __DIR__ . '/../src/Command');
    $services->load('DR\GitCommitNotification\Form\\', __DIR__ . '/../src/Form');
    $services->load('DR\GitCommitNotification\EventSubscriber\\', __DIR__ . '/../src/EventSubscriber');
    $services->load('DR\GitCommitNotification\Service\\', __DIR__ . '/../src/Service')
        ->exclude('../src/Service/Parser/{DiffParser.php,DiffFileParser.php}');
    $services->load('DR\GitCommitNotification\Twig\\', __DIR__ . '/../src/Twig/*Extension.php');
    $services->load('DR\GitCommitNotification\ExternalTool\\', __DIR__ . '/../src/ExternalTool');
    $services->load('DR\GitCommitNotification\Repository\\', __DIR__ . '/../src/Repository');

    $services->set(Filesystem::class);
    $services->set(InputValidator::class);
    $services->set(LoginService::class);
    $services->set(User::class)->public()->factory([service(Security::class), 'getUser']);

    // Register AzureAd provider, for SSO
    $services->set(Azure::class)
        ->arg(
            '$options',
            [
                'tenant'                 => '%env(OAUTH_AZURE_AD_TENANT_ID)%',
                'clientId'               => '%env(OAUTH_AZURE_AD_CLIENT_ID)%',
                'clientSecret'           => '%env(OAUTH_AZURE_AD_CLIENT_SECRET)%',
                // scopes: https://docs.microsoft.com/en-us/azure/active-directory/develop/v2-permissions-and-consent#permission-types
                'scopes'                 => ['openid', 'profile'],
                'defaultEndPointVersion' => '2.0'
            ]
        );
    $services->set(AzureAdUserBadgeFactory::class);
    $services->set(AzureAdAuthenticator::class);

    $services->set(DiffParser::class);
    $services->set(DiffFileParser::class);
    $services->set(DiffChangeBundler::class);
    $services->set(DiffLineDiffer::class);
    $services->set(CssToInlineStyles::class);
    $services->set(HighlighterFactory::class);
    $services->set(GitDiffCommandBuilder::class)->arg('$git', '%env(GIT_BINARY)%');
    $services->set(GitLogCommandBuilder::class)->arg('$git', '%env(GIT_BINARY)%');

    // custom register GitRepositoryService with cache dir
    $services->set(CacheableGitRepositoryService::class)->arg('$cacheDirectory', "%kernel.cache_dir%");

    // Register Git
    $services->set(CliRunner::class)->arg('$gitBinary', '%env(GIT_BINARY)%');
    $services->set(Git::class)->arg('$runner', service(CliRunner::class));
};
