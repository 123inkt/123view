<?php
declare(strict_types=1);

use DR\GitCommitNotification\Repository\Config\ExternalLinkRepository;
use DR\GitCommitNotification\Repository\Config\RuleRepository;
use DR\GitCommitNotification\Service\Git\CacheableGitRepositoryService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // make these services public to allow mocking in E2E/Integration test
    $services->get(RuleRepository::class)->public();
    $services->get(ExternalLinkRepository::class)->public();
    $services->get(CacheableGitRepositoryService::class)->public();
};
