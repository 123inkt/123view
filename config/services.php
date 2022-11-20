<?php

declare(strict_types=1);

use CzProject\GitPhp\Git;
use CzProject\GitPhp\Runners\CliRunner;
use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\GitCommitNotification\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\GitCommitNotification\MessageHandler\MailNotificationMessageHandler;
use DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\GitCommandBuilderFactory;
use DR\GitCommitNotification\Service\Git\GitRepositoryLockManager;
use DR\GitCommitNotification\Service\Git\GitRepositoryService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\LockableReviewDiffService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffService;
use DR\GitCommitNotification\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\GitCommitNotification\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\GitCommitNotification\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use DR\GitCommitNotification\Service\Revision\RevisionPatternMatcher;
use DR\GitCommitNotification\Service\Webhook\WebhookExecutionService;
use DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles;
use Highlight\Highlighter;
use League\CommonMark\GithubFlavoredMarkdownConverter;
use League\CommonMark\MarkdownConverter;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\Security\Core\Security;
use TheNetworg\OAuth2\Client\Provider\Azure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()->set('timezone', '%env(APP_TIMEZONE)%');
    $services = $container->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$allowCustomRecipients', '%env(bool:ALLOW_CUSTOM_RECIPIENTS_PER_RULE)%')
        ->bind('$upsourceApiUrl', '%env(UPSOURCE_API_URL)%')
        ->bind('$gitlabApiUrl', '%env(GITLAB_API_URL)%')
        ->bind('$codeReviewExcludeAuthors', '%env(CODE_REVIEW_EXCLUDE_AUTHORS)%');

    // Register controllers
    $services->load('DR\GitCommitNotification\Controller\\', '../src/Controller/**/*Controller.php')->tag('controller.service_arguments');

    // auto-wire commands, services and twig-extensions
    $services->load('DR\GitCommitNotification\Command\\', __DIR__ . '/../src/Command');
    $services->load('DR\GitCommitNotification\EventSubscriber\\', __DIR__ . '/../src/EventSubscriber');
    $services->load('DR\GitCommitNotification\Form\\', __DIR__ . '/../src/Form');
    $services->load('DR\GitCommitNotification\Service\\', __DIR__ . '/../src/Service')
        ->exclude('../src/Service/Parser/{DiffParser.php,DiffFileParser.php}');
    $services->load('DR\GitCommitNotification\Twig\\', __DIR__ . '/../src/Twig/*Extension.php');
    $services->load('DR\GitCommitNotification\ExternalTool\\', __DIR__ . '/../src/ExternalTool');
    $services->load('DR\GitCommitNotification\MessageHandler\\', __DIR__ . '/../src/MessageHandler');
    $services->load('DR\GitCommitNotification\Repository\\', __DIR__ . '/../src/Repository');
    $services->load('DR\GitCommitNotification\Request\\', __DIR__ . '/../src/Request');
    $services->load('DR\GitCommitNotification\Security\Voter\\', __DIR__ . '/../src/Security/Voter');
    $services->load('DR\GitCommitNotification\ViewModelProvider\\', __DIR__ . '/../src/ViewModelProvider');

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
    $services->set(Highlighter::class);
    $services->set(MarkdownConverter::class, GithubFlavoredMarkdownConverter::class)
        ->arg('$config', ['html_input' => 'strip', 'allow_unsafe_links' => false]);
    $services->set(GitCommandBuilderFactory::class)->arg('$git', '%env(GIT_BINARY)%');

    // custom register cache dir
    $services->set(CacheableGitRepositoryService::class)->arg('$cacheDirectory', "%kernel.project_dir%/var/git");
    $services->set(GitRepositoryService::class)->arg('$cacheDirectory', "%kernel.project_dir%/var/git");
    $services->set(GitRepositoryLockManager::class)->arg('$cacheDirectory', "%kernel.project_dir%/var/git");

    // custom register with matching pattern
    $services->set(RevisionPatternMatcher::class)
        ->arg('$matchingPattern', '%env(CODE_REVIEW_MATCHING_PATTERN)%')
        ->arg('$matchingGroups', '%env(CODE_REVIEW_MATCHING_GROUPS)%');

    // Register Git
    $services->set(CliRunner::class)->arg('$gitBinary', '%env(GIT_BINARY)%');
    $services->set(Git::class)->arg('$runner', service(CliRunner::class));

    // Review diff strategies
    $services->set(BasicCherryPickStrategy::class)->tag('review_diff_strategy', ['priority' => 30]);
    $services->set(HesitantCherryPickStrategy::class)->tag('review_diff_strategy', ['priority' => 10]);
    $services->set('review.diff.service', ReviewDiffService::class)->arg('$reviewDiffStrategies', tagged_iterator('review_diff_strategy'));

    $services->set('lock.review.diff.service', LockableReviewDiffService::class)->arg('$diffService', service('review.diff.service'));
    $services->set(ReviewDiffServiceInterface::class, CacheableReviewDiffService::class)->arg('$diffService', service('lock.review.diff.service'));

    // Mail Notification Message handlers
    $services->set(CommentAddedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentUpdatedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentReplyAddedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentReplyUpdatedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentResolvedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(MailNotificationHandlerProvider::class)->args([tagged_iterator('mail_notification_handler', null, 'accepts')]);
    $services->set(MailNotificationMessageHandler::class)->arg('$mailNotificationDelay', '%env(MAILER_NOTIFICATION_DELAY)%');

    $services->set(WebhookExecutionService::class)->arg('$httpClient', inline_service(NativeHttpClient::class));
};
