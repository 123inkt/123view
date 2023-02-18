<?php

declare(strict_types=1);

use cogpowered\FineDiff\Diff;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\Runners\CliRunner;
use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\Review\Entity\User\User;
use DR\Review\EventSubscriber\ContentSecurityPolicyResponseSubscriber;
use DR\Review\Git\Diff\DiffChangeBundler;
use DR\Review\Git\Diff\DiffLineDiffer;
use DR\Review\Git\Diff\Opcode\DiffChangeFactory;
use DR\Review\Git\Diff\Opcode\DiffChangeOptimizer;
use DR\Review\Git\Diff\Opcode\DiffGranularity;
use DR\Review\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\Review\MessageHandler\MailNotificationMessageHandler;
use DR\Review\Router\ReviewRouter;
use DR\Review\Security\AzureAd\AzureAdAuthenticator;
use DR\Review\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\Review\Security\AzureAd\LoginService;
use DR\Review\Security\UserChecker;
use DR\Review\Service\CodeReview\Comment\CommonMarkdownConverter;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\GitRepositoryService;
use DR\Review\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\LockableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\Review\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\Review\Service\Parser\DiffFileParser;
use DR\Review\Service\Parser\DiffParser;
use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Service\Webhook\WebhookExecutionService;
use DR\Review\Twig\InlineCss\CssToInlineStyles;
use Highlight\Highlighter;
use League\CommonMark\MarkdownConverter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\NativeHttpClient;
use TheNetworg\OAuth2\Client\Provider\Azure;
use function Symfony\Component\DependencyInjection\Loader\Configurator\inline_service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $container): void {
    $container->parameters()->set('timezone', '%env(APP_TIMEZONE)%');
    $container->parameters()->set('locale', '%env(APP_LOCALE)%');
    $services = $container->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$allowCustomRecipients', '%env(bool:ALLOW_CUSTOM_RECIPIENTS_PER_RULE)%')
        ->bind('$gitlabApiUrl', '%env(GITLAB_API_URL)%')
        ->bind('$applicationName', '%env(APP_NAME)%')
        ->bind('$codeReviewExcludeAuthors', '%env(CODE_REVIEW_EXCLUDE_AUTHORS)%');

    // Register controllers
    $services->load('DR\Review\Controller\\', '../src/Controller/**/*Controller.php')->tag('controller.service_arguments');

    // auto-wire commands, services and twig-extensions
    $services->load('DR\Review\ApiPlatform\Provider\\', __DIR__ . '/../src/ApiPlatform/Provider');
    $services->load('DR\Review\Command\\', __DIR__ . '/../src/Command');
    $services->load('DR\Review\EventSubscriber\\', __DIR__ . '/../src/EventSubscriber');
    $services->load('DR\Review\Form\\', __DIR__ . '/../src/Form');
    $services->load('DR\Review\Service\\', __DIR__ . '/../src/Service')
        ->exclude('../src/Service/Parser/{DiffParser.php,DiffFileParser.php}');
    $services->load('DR\Review\Twig\\', __DIR__ . '/../src/Twig/*Extension.php');
    $services->load('DR\Review\ExternalTool\\', __DIR__ . '/../src/ExternalTool');
    $services->load('DR\Review\MessageHandler\\', __DIR__ . '/../src/MessageHandler');
    $services->load('DR\Review\Repository\\', __DIR__ . '/../src/Repository');
    $services->load('DR\Review\Request\\', __DIR__ . '/../src/Request');
    $services->load('DR\Review\Security\Voter\\', __DIR__ . '/../src/Security/Voter');
    $services->load('DR\Review\ViewModelProvider\\', __DIR__ . '/../src/ViewModelProvider');

    $services->set(Filesystem::class);
    $services->set(InputValidator::class);
    $services->set(LoginService::class);
    $services->set(UserChecker::class);
    $services->set(User::class)->public()->factory([service(Security::class), 'getUser']);
    $services->set(ContentSecurityPolicyResponseSubscriber::class)->arg('$hostname', '%env(APP_HOSTNAME)%');

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
    $services->set(AzureAdAuthenticator::class)->arg('$authenticationEnabled', '%env(bool:APP_AUTH_AZURE_AD)%');

    $services->set(DiffParser::class);
    $services->set(DiffFileParser::class);
    $services->set(DiffChangeBundler::class);
    $services->set(DiffChangeFactory::class);
    $services->set(DiffChangeOptimizer::class);
    $services->set(Diff::class)->arg('$granularity', inline_service(DiffGranularity::class));
    $services->set(DiffLineDiffer::class);
    $services->set(CssToInlineStyles::class);
    $services->set(Highlighter::class);
    $services->set(MarkdownConverter::class, CommonMarkdownConverter::class);
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
    $services->set(ReviewRouter::class)->decorate('router')->args([service('.inner')]);

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
