<?php

declare(strict_types=1);

use DR\GitCommitNotification\Doctrine\Type\DiffAlgorithmType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->extension(
        'doctrine',
        [
            'dbal' => [
                'url' => '%env(resolve:DATABASE_URL)%',
                'types' => ['enum_diff_algorithm' => DiffAlgorithmType::class]
            ],
            'orm'  => [
                'auto_generate_proxy_classes' => true,
                'naming_strategy'             => 'doctrine.orm.naming_strategy.underscore_number_aware',
                'auto_mapping'                => true,
                'mappings'                    => [
                    'DR\GitCommitNotification' => [
                        'is_bundle' => false,
                        'dir'       => '%kernel.project_dir%/src/Entity',
                        'prefix'    => 'DR\GitCommitNotification\Entity',
                        'alias'     => 'DR\GitCommitNotification'
                    ]
                ]
            ]
        ]
    );
};
