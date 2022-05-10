<?php

declare(strict_types=1);

use CzProject\GitPhp\Git;
use CzProject\GitPhp\Runners\CliRunner;
use Doctrine\Common\Annotations\AnnotationReader;
use DR\GitCommitNotification\Git\Diff\DiffChangeBundler;
use DR\GitCommitNotification\Git\Diff\DiffLineDiffer;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use DR\GitCommitNotification\Service\Git\Diff\GitDiffCommandBuilder;
use DR\GitCommitNotification\Service\Git\Log\GitLogCommandBuilder;
use DR\GitCommitNotification\Service\Parser\DiffFileParser;
use DR\GitCommitNotification\Service\Parser\DiffParser;
use DR\GitCommitNotification\Twig\Highlight\HighlighterFactory;
use DR\GitCommitNotification\Twig\InlineCss\CssToInlineStyles;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Loader\LoaderInterface;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->bind('$upsourceApiUrl', '%env(UPSOURCE_API_URL)%')
        ->bind('$gitlabApiUrl', '%env(GITLAB_API_URL)%');

    // Register controllers
    $services->load('DR\GitCommitNotification\Controller\\', '../src/Controller/*Controller.php')->tag('controller.service_arguments');

    // auto-wire commands, services and twig-extensions
    $services->load('DR\GitCommitNotification\Command\\', __DIR__ . '/../src/Command');
    $services->load('DR\GitCommitNotification\Service\\', __DIR__ . '/../src/Service')
        ->exclude('../src/Service/Parser/{DiffParser.php,DiffFileParser.php}');
    $services->load('DR\GitCommitNotification\Twig\\', __DIR__ . '/../src/Twig/*Extension.php');
    $services->load('DR\GitCommitNotification\ExternalTool\\', __DIR__ . '/../src/ExternalTool');

    $services->set(DiffParser::class);
    $services->set(DiffFileParser::class);
    $services->set(DiffChangeBundler::class);
    $services->set(DiffLineDiffer::class);
    $services->set(CssToInlineStyles::class);
    $services->set(HighlighterFactory::class);
    $services->set(GitDiffCommandBuilder::class)->arg('$git', '%env(GIT_BINARY)%');
    $services->set(GitLogCommandBuilder::class)->arg('$git', '%env(GIT_BINARY)%');

    // custom register GitRepositoryService with cache dir
    $services->set(CacheableGitRepositoryService::class)
        ->public()
        ->arg('$cacheDirectory', "%kernel.cache_dir%");

    // Register Git
    $services->set(CliRunner::class)->arg('$gitBinary', '%env(GIT_BINARY)%');
    $services->set(Git::class)->arg('$runner', service(CliRunner::class));

    // Register Symfony Filesystem
    $services->set(Filesystem::class);

    // Register Symfony Serializer and configuration
    $services->set(XmlEncoder::class);
    $services->set(AnnotationReader::class);
    $services->set(AnnotationLoader::class)->alias(LoaderInterface::class, AnnotationLoader::class);
    $services->set(ClassMetadataFactory::class);
    $services->set(MetadataAwareNameConverter::class);
    $services->set(ArrayDenormalizer::class);
    $services->set(ReflectionExtractor::class);
    $services->set(ObjectNormalizer::class)
        ->arg('$classMetadataFactory', service(ClassMetadataFactory::class))
        ->arg('$nameConverter', service(MetadataAwareNameConverter::class))
        ->arg('$propertyTypeExtractor', service(ReflectionExtractor::class));

    $services->set(Serializer::class)
        ->arg('$normalizers', [service(ArrayDenormalizer::class), service(ObjectNormalizer::class)])
        ->arg('$encoders', [service(XmlEncoder::class)]);
};
