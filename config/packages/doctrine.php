<?php

declare(strict_types=1);

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use DR\GitCommitNotification\Doctrine\Type\FilterType;
use DR\GitCommitNotification\Doctrine\Type\FrequencyType;
use DR\GitCommitNotification\Doctrine\Type\MailThemeType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Config\DoctrineConfig;

return static function (ContainerConfigurator $containerConfigurator, DoctrineConfig $doctrineConfig): void {
    $containerConfigurator->extension(
        'doctrine',
        [
            'dbal' => [
                'url'            => '%env(resolve:DATABASE_URL)%',
                'server_version' => '%env(MYSQL_VERSION)%',
                'mapping_types'  => [
                    'enum' => 'string'
                ],
                'types'          => [
                    FrequencyType::TYPE     => FrequencyType::class,
                    DiffAlgorithmType::TYPE => DiffAlgorithmType::class,
                    MailThemeType::TYPE     => MailThemeType::class,
                    FilterType::TYPE        => FilterType::class
                ]
            ]
        ]
    );

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
