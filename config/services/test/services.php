<?php
declare(strict_types=1);

use DR\Review\Entity\User\User;
use DR\Review\Repository\Config\ExternalLinkRepository;
use DR\Review\Repository\Config\RuleRepository;
use DR\Review\Service\Git\CacheableGitRepositoryService;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Service\Revision\RevisionFetchService;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // make these services public to allow mocking in E2E/Integration test
    $services->get(RuleRepository::class)->public();
    $services->get(ExternalLinkRepository::class)->public();
    $services->get(CacheableGitRepositoryService::class)->public();
    $services->get(RevisionFetchService::class)->public();
    $services->set(User::class)->public();
    $services->set(RemoteEventHandler::class)->public();
};
