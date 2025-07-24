<?php

declare(strict_types=1);

use ApiPlatform\Doctrine\Orm\State\CollectionProvider;
use ApiPlatform\State\ProviderInterface;
use CzProject\GitPhp\Git;
use CzProject\GitPhp\Runners\CliRunner;
use DigitalRevolution\SymfonyConsoleValidation\InputValidator;
use DR\JBDiff\JBDiff;
use DR\Review\ApiPlatform\OpenApi\OpenApiFactory;
use DR\Review\ApiPlatform\OpenApi\OperationParameterDocumentor;
use DR\Review\Entity\User\User;
use DR\Review\EventSubscriber\ContentSecurityPolicyResponseSubscriber;
use DR\Review\ExternalTool\Gitlab\GitlabService;
use DR\Review\Form\User\UserSettingType;
use DR\Review\MessageHandler\Mail\CommentAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyAddedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentReplyUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentResolvedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\CommentUpdatedMailNotificationHandler;
use DR\Review\MessageHandler\Mail\MailNotificationHandlerProvider;
use DR\Review\MessageHandler\MailNotificationMessageHandler;
use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\QueryParser\ParserHasFailedFormatter;
use DR\Review\Response\ProblemJsonResponseFactory;
use DR\Review\Router\ReviewRouter;
use DR\Review\Security\Api\BearerAuthenticator;
use DR\Review\Security\AzureAd\AzureAdAuthenticator;
use DR\Review\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\Review\Security\AzureAd\LoginService;
use DR\Review\Security\UserChecker;
use DR\Review\Service\Api\Gitlab\GitlabApi;
use DR\Review\Service\Api\Gitlab\OAuth2ProviderFactory;
use DR\Review\Service\CodeReview\CodeReviewFileService;
use DR\Review\Service\CodeReview\Comment\CommonMarkdownConverter;
use DR\Review\Service\Git\GitCommandBuilderFactory;
use DR\Review\Service\Git\GitRepositoryLocationService;
use DR\Review\Service\Git\GitRepositoryLockManager;
use DR\Review\Service\Git\Review\ReviewDiffService\CacheableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\LockableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\RecoverableReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffService;
use DR\Review\Service\Git\Review\ReviewDiffService\ReviewDiffServiceInterface;
use DR\Review\Service\Git\Review\Strategy\BasicCherryPickStrategy;
use DR\Review\Service\Git\Review\Strategy\HesitantCherryPickStrategy;
use DR\Review\Service\Git\Review\Strategy\PersistentCherryPickStrategy;
use DR\Review\Service\Health\DoctrineDbal;
use DR\Review\Service\Health\MercureHub;
use DR\Review\Service\Health\OpcacheInternedStrings;
use DR\Review\Service\Notification\RuleNotificationTokenGenerator;
use DR\Review\Service\Parser\DiffFileParser;
use DR\Review\Service\Parser\DiffParser;
use DR\Review\Service\Parser\PrunableDiffParser;
use DR\Review\Service\RemoteEvent\Gitlab\ApprovedMergeRequestEventHandler;
use DR\Review\Service\RemoteEvent\Gitlab\PushEventHandler;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Service\Report\CodeInspection\CodeInspectionIssueParserProvider;
use DR\Review\Service\Report\CodeInspection\Parser\CheckStyleIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\GitlabIssueParser;
use DR\Review\Service\Report\CodeInspection\Parser\JunitIssueParser;
use DR\Review\Service\Report\Coverage\CodeCoverageParserProvider;
use DR\Review\Service\Report\Coverage\Parser\CloverParser;
use DR\Review\Service\Revision\RevisionPatternMatcher;
use DR\Review\Service\Search\RipGrep\GitFileSearcher;
use DR\Review\Service\Search\RipGrep\SearchResultLineParser;
use DR\Review\Service\User\IdeUrlPatternProvider;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Service\Webhook\WebhookExecutionService;
use DR\Review\Twig\IdeButtonExtension;
use DR\Review\Twig\InlineCss\CssToInlineStyles;
use DR\Review\ViewModelProvider\Appender\Review\BranchReviewViewModelAppender;
use DR\Review\ViewModelProvider\Appender\Review\FileDiffViewModelAppender;
use DR\Review\ViewModelProvider\Appender\Review\FileTreeViewModelAppender;
use DR\Review\ViewModelProvider\Appender\Review\ReviewSummaryViewModelAppender;
use DR\Review\ViewModelProvider\Appender\Review\RevisionViewModelAppender;
use DR\Review\ViewModelProvider\ReviewViewModelProvider;
use Highlight\Highlighter;
use League\CommonMark\MarkdownConverter;
use League\OAuth2\Client\Provider\GenericProvider;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpClient\NativeHttpClient;
use Symfony\Component\HttpKernel\CacheClearer\Psr6CacheClearer;
use Symfony\Contracts\Cache\CacheInterface;
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
        ->bind('$gitlabCommentSyncEnabled', '%env(bool:GITLAB_COMMENT_SYNC)%')
        ->bind('$gitlabReviewerSyncEnabled', '%env(bool:GITLAB_REVIEWER_SYNC)%')
        ->bind('$gitlabApiUrl', '%env(GITLAB_API_URL)%')
        ->bind('$applicationName', '%env(APP_NAME)%')
        ->bind('$appAbsoluteUrl', '%env(APP_ABSOLUTE_URL)%')
        ->bind('$codeReviewExcludeAuthors', '%env(CODE_REVIEW_EXCLUDE_AUTHORS)%')
        ->bind(ProviderInterface::class . ' $collectionProvider', service(CollectionProvider::class));

    // Register controllers
    $services->load('DR\Review\Controller\\', '../src/Controller/**/*Controller.php')->tag('controller.service_arguments');

    // auto-wire commands, services and twig-extensions
    $services->load('DR\Review\ApiPlatform\Factory\\', __DIR__ . '/../src/ApiPlatform/Factory');
    $services->load('DR\Review\ApiPlatform\Provider\\', __DIR__ . '/../src/ApiPlatform/Provider');
    $services->load('DR\Review\ApiPlatform\StateProcessor\\', __DIR__ . '/../src/ApiPlatform/StateProcessor');
    $services->load('DR\Review\Command\\', __DIR__ . '/../src/Command');
    $services->load('DR\Review\EventSubscriber\\', __DIR__ . '/../src/EventSubscriber');
    $services->load('DR\Review\Form\\', __DIR__ . '/../src/Form');
    $services->load('DR\Review\Service\\', __DIR__ . '/../src/Service')
        ->exclude('../src/Service/Parser/{DiffParser.php,DiffFileParser.php}');
    $services->load('DR\Review\Twig\\', __DIR__ . '/../src/Twig/*Extension.php');
    $services->load('DR\Review\ExternalTool\\', __DIR__ . '/../src/ExternalTool');
    $services->load('DR\Review\MessageHandler\\', __DIR__ . '/../src/MessageHandler');
    $services->load('DR\Review\RemoteEventConsumer\\', __DIR__ . '/../src/RemoteEventConsumer');
    $services->load('DR\Review\Repository\\', __DIR__ . '/../src/Repository');
    $services->load('DR\Review\Request\\', __DIR__ . '/../src/Request');
    $services->load('DR\Review\Security\Voter\\', __DIR__ . '/../src/Security/Voter');
    $services->load('DR\Review\ViewModelProvider\\', __DIR__ . '/../src/ViewModelProvider');
    $services->load('DR\Review\Webhook\\', __DIR__ . '/../src/Webhook');

    // create empty cache clearer
    $services->set('cache.default_clearer', Psr6CacheClearer::class)->args([[]]);

    $services->set(Filesystem::class);
    $services->set(InputValidator::class);
    $services->set(LoginService::class);
    $services->set(UserChecker::class);
    $services->set(UserEntityProvider::class);
    $services->set(User::class)->public()->factory([service(UserEntityProvider::class), 'getUser']);
    $services->set(ContentSecurityPolicyResponseSubscriber::class)
        ->arg('$hostname', '%env(APP_HOSTNAME)%')
        ->arg('$ideUrlEnabled', '%env(bool:IDE_URL_ENABLED)%');
    $services->set(ProblemJsonResponseFactory::class)->arg('$debug', '%env(APP_DEBUG)%');

    // Configure Api
    $services->set(OperationParameterDocumentor::class);
    $services->set(OpenApiFactory::class)
        ->decorate('api_platform.openapi.factory')
        ->args([service('.inner')])
        ->autoconfigure(false);
    $services->set(BearerAuthenticator::class);

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

    $services->set(PrunableDiffParser::class);
    $services->set(DiffParser::class);
    $services->set(DiffFileParser::class);
    $services->set(JBDiff::class);
    $services->set(CssToInlineStyles::class);
    $services->set(IdeButtonExtension::class)->args(['%env(bool:IDE_URL_ENABLED)%', '%env(IDE_URL_TITLE)%']);
    $services->set(Highlighter::class);
    $services->set(MarkdownConverter::class, CommonMarkdownConverter::class);
    $services->set(GitCommandBuilderFactory::class)->arg('$git', '%env(GIT_BINARY)%');
    $services->set(ParserHasFailedFormatter::class);
    $services->set(RuleNotificationTokenGenerator::class)->arg('$appSecret', '%env(APP_SECRET)%');
    $services->set(UserSettingType::class)->arg('$ideUrlPattern', '%env(IDE_URL_PATTERN)%');
    $services->set(IdeUrlPatternProvider::class)->arg('$ideUrlPattern', '%env(IDE_URL_PATTERN)%');

    // custom register cache dir
    $services->set(GitRepositoryLockManager::class)->arg('$cacheDirectory', "%kernel.project_dir%/var/git/");
    $services->set(GitRepositoryLocationService::class)->arg('$cacheDirectory', "%kernel.project_dir%/var/git/");
    $services->set(GitFileSearcher::class)->arg('$gitCacheDirectory', "%kernel.project_dir%/var/git/");
    $services->set(SearchResultLineParser::class)->arg('$gitCacheDirectory', "%kernel.project_dir%/var/git/");

    // custom register with matching pattern
    $services->set(RevisionPatternMatcher::class)
        ->arg('$matchingPattern', '%env(CODE_REVIEW_MATCHING_PATTERN)%')
        ->arg('$matchingGroups', '%env(CODE_REVIEW_MATCHING_GROUPS)%');

    // Register Git
    $services->set(CliRunner::class)->arg('$gitBinary', '%env(GIT_BINARY)%');
    $services->set(Git::class)->arg('$runner', service(CliRunner::class));

    // Review diff strategies
    $services->set(BasicCherryPickStrategy::class)->tag('review_diff_strategy', ['priority' => 30]);
    $services->set(PersistentCherryPickStrategy::class)->tag('review_diff_strategy', ['priority' => 20]);
    $services->set(HesitantCherryPickStrategy::class)->tag('review_diff_strategy', ['priority' => 10]);
    $services->set('review.diff.service', ReviewDiffService::class)->arg('$reviewDiffStrategies', tagged_iterator('review_diff_strategy'));

    $services->set('recoverable.review.diff.service', RecoverableReviewDiffService::class)->arg('$diffService', service('review.diff.service'));
    $services->set('lock.review.diff.service', LockableReviewDiffService::class)->arg('$diffService', service('recoverable.review.diff.service'));
    $services->set(ReviewDiffServiceInterface::class, CacheableReviewDiffService::class)->arg('$diffService', service('lock.review.diff.service'));
    $services->set(ReviewRouter::class)->decorate('router')->args([service('.inner')]);
    $services->set(CodeReviewFileService::class)->arg('$revisionCache', service(CacheInterface::class . ' $revisionCache'));

    // Code inspection parsers
    $services->set(CheckStyleIssueParser::class)->tag('code_inspection_issue_parser', ['key' => CheckStyleIssueParser::FORMAT]);
    $services->set(GitlabIssueParser::class)->tag('code_inspection_issue_parser', ['key' => GitlabIssueParser::FORMAT]);
    $services->set(JunitIssueParser::class)->tag('code_inspection_issue_parser', ['key' => JunitIssueParser::FORMAT]);
    $services->set(CodeInspectionIssueParserProvider::class)->arg('$parsers', tagged_iterator('code_inspection_issue_parser', 'key'));

    // Code coverage parsers
    $services->set(CloverParser::class)->tag('code_coverage_parser', ['key' => CloverParser::FORMAT]);
    $services->set(CodeCoverageParserProvider::class)->arg('$parsers', tagged_iterator('code_coverage_parser', 'key'));

    // Mail Notification Message handlers
    $services->set(CommentAddedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentUpdatedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentReplyAddedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentReplyUpdatedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(CommentResolvedMailNotificationHandler::class)->tag('mail_notification_handler');
    $services->set(MailNotificationHandlerProvider::class)->args([tagged_iterator('mail_notification_handler', null, 'accepts')]);
    $services->set(MailNotificationMessageHandler::class)->arg('$mailNotificationDelay', '%env(MAILER_NOTIFICATION_DELAY)%');

    // Webhook handlers
    $services->set(ApprovedMergeRequestEventHandler::class)->tag('webhook_handler', ['key' => MergeRequestEvent::class]);
    $services->set(PushEventHandler::class)->tag('webhook_handler', ['key' => PushEvent::class]);
    $services->set(RemoteEventHandler::class)->arg('$handlers', tagged_iterator('webhook_handler', 'key'));

    $services->set(WebhookExecutionService::class)->arg('$httpClient', inline_service(NativeHttpClient::class));

    // Gitlab integration
    $services->set(GitlabApi::class);
    $services->set(GitlabService::class);

    $services->set(OAuth2ProviderFactory::class)
        ->arg('$gitlabApplicationId', '%env(GITLAB_APPLICATION_ID)%')
        ->arg('$gitlabApplicationSecret', '%env(GITLAB_APPLICATION_SECRET)%');
    $services->set(GenericProvider::class . ' $gitlabOAuth2Provider', GenericProvider::class)
        ->factory([service(OAuth2ProviderFactory::class), 'create']);

    $services->set(DoctrineDbal::class)->tag('liip_monitor.check');
    $services->set(OpcacheInternedStrings::class)->tag('liip_monitor.check');
    $services->set(MercureHub::class)->tag('liip_monitor.check');

    // view model appenders
    $services->set(FileTreeViewModelAppender::class)->tag('review.view_model_appender');
    $services->set(RevisionViewModelAppender::class)->tag('review.view_model_appender');
    $services->set(ReviewSummaryViewModelAppender::class)->tag('review.view_model_appender');
    $services->set(FileDiffViewModelAppender::class)->tag('review.view_model_appender');
    $services->set(BranchReviewViewModelAppender::class)->tag('review.view_model_appender');
    $services->set(ReviewViewModelProvider::class)->arg('$reviewViewModelAppenders', tagged_iterator('review.view_model_appender'));
};
