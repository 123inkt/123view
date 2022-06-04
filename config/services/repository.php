<?php
declare(strict_types=1);

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Entity\Repository;
use DR\GitCommitNotification\Repository\RepositoryRepository;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    // doctrine
    $services->set(RepositoryRepository::class)
        ->factory([service(ManagerRegistry::class), 'getRepository'])
        ->arg('$persistentObject', Repository::class);
};
