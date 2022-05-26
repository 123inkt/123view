<?php

declare(strict_types=1);

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $doctrineConfig): void {
    $doctrineConfig->dbal()->defaultConnection('default');
    $dbal = $doctrineConfig->dbal()->connection('default');
    $dbal->url('%env(resolve:DATABASE_URL)%');
    $dbal->mappingType(DiffAlgorithmType::TYPE, DiffAlgorithmType::class);
    $dbal->mappingType('enum', 'string');

    $doctrineConfig->orm()->autoGenerateProxyClasses(true);
    $doctrineConfig->orm()->defaultEntityManager('default');

    $em = $doctrineConfig->orm()->entityManager('default');
    $em->autoMapping(true);
    $em->connection('default');
    $em->namingStrategy('doctrine.orm.naming_strategy.underscore_number_aware');
    $em->mapping('DR\GitCommitNotification')
        ->isBundle(false)
        ->dir('%kernel.project_dir%/src/Entity')
        ->prefix('DR\GitCommitNotification\Entity')
        ->alias('DR\GitCommitNotification');
};
