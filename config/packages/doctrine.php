<?php

declare(strict_types=1);

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\ColorThemeType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Doctrine\Type\FilterType;
use DR\Review\Doctrine\Type\FrequencyType;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Doctrine\Type\NotificationStatusType;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Doctrine\Type\UriType;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;
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
                    UriType::TYPE                       => UriType::class,
                    FrequencyType::TYPE                 => FrequencyType::class,
                    DiffAlgorithmType::TYPE             => DiffAlgorithmType::class,
                    MailThemeType::TYPE                 => MailThemeType::class,
                    FilterType::TYPE                    => FilterType::class,
                    CodeReviewStateType::TYPE           => CodeReviewStateType::class,
                    CodeReviewerStateType::TYPE         => CodeReviewerStateType::class,
                    CommentStateType::TYPE              => CommentStateType::class,
                    NotificationStatusType::TYPE        => NotificationStatusType::class,
                    ColorThemeType::TYPE                => ColorThemeType::class,
                    SpaceSeparatedStringValueType::TYPE => SpaceSeparatedStringValueType::class
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
    $em->mapping('DR\Review')
        ->isBundle(false)
        ->dir('%kernel.project_dir%/src/Entity')
        ->prefix('DR\Review\Entity')
        ->alias('DR\Review');

    $em->dql()->stringFunction('JSON_CONTAINS', JsonContains::class);
};
