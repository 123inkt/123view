<?php
declare(strict_types=1);

use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // make these service public to allow mocking in E2E test
    $services->set(RuleRepository::class)->public();
    $services->set(ExternalLinkRepository::class)->public();

    // custom register GitRepositoryService with cache dir
    $services->set(CacheableGitRepositoryService::class)->public();
};
