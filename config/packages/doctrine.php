<?php

declare(strict_types=1);

use DR\Review\Doctrine\Type\AuthenticationType;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Doctrine\Type\CodeReviewStateType;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Doctrine\Type\ColorThemeType;
use DR\Review\Doctrine\Type\CommentStateType;
use DR\Review\Doctrine\Type\CommentTagType;
use DR\Review\Doctrine\Type\DiffAlgorithmType;
use DR\Review\Doctrine\Type\FilterType;
use DR\Review\Doctrine\Type\FrequencyType;
use DR\Review\Doctrine\Type\LineCoverageType;
use DR\Review\Doctrine\Type\MailThemeType;
use DR\Review\Doctrine\Type\NotificationSendType;
use DR\Review\Doctrine\Type\NotificationStatusType;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Doctrine\Type\SpaceSeparatedStringValueType;
use DR\Review\Doctrine\Type\UriType;
use Scienta\DoctrineJsonFunctions\Query\AST\Functions\Mysql\JsonContains;
use Symfony\Component\DependencyInjection\Loader\Configurator\App;

return App::config([
    'doctrine' => [
        'dbal' => [
            'connections' => [
                'default' => [
                    'url'            => '%env(resolve:DATABASE_URL)%',
                    'server_version' => '%env(MYSQL_VERSION)%',
                    'mapping_types'  => ['enum' => 'string'],
                ],
            ],
            'types'       => [
                AuthenticationType::TYPE            => ['class' => AuthenticationType::class],
                CodeReviewStateType::TYPE           => ['class' => CodeReviewStateType::class],
                CodeReviewType::TYPE                => ['class' => CodeReviewType::class],
                CodeReviewerStateType::TYPE         => ['class' => CodeReviewerStateType::class],
                ColorThemeType::TYPE                => ['class' => ColorThemeType::class],
                CommentStateType::TYPE              => ['class' => CommentStateType::class],
                CommentTagType::TYPE                => ['class' => CommentTagType::class],
                DiffAlgorithmType::TYPE             => ['class' => DiffAlgorithmType::class],
                FilterType::TYPE                    => ['class' => FilterType::class],
                FrequencyType::TYPE                 => ['class' => FrequencyType::class],
                LineCoverageType::TYPE              => ['class' => LineCoverageType::class],
                MailThemeType::TYPE                 => ['class' => MailThemeType::class],
                NotificationSendType::TYPE          => ['class' => NotificationSendType::class],
                NotificationStatusType::TYPE        => ['class' => NotificationStatusType::class],
                RepositoryGitType::TYPE             => ['class' => RepositoryGitType::class],
                SpaceSeparatedStringValueType::TYPE => ['class' => SpaceSeparatedStringValueType::class],
                UriType::TYPE                       => ['class' => UriType::class],
            ],
        ],
        'orm'  => [
            'default_entity_manager' => 'default',
            'entity_managers'        => [
                'default' => [
                    'auto_mapping'    => true,
                    'connection'      => 'default',
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                    'mappings'        => [
                        'DR\Review' => [
                            'is_bundle' => false,
                            'dir'       => '%kernel.project_dir%/src/Entity',
                            'prefix'    => 'DR\Review\Entity',
                            'alias'     => 'DR\Review',
                        ],
                    ],
                    'dql'             => ['string_functions' => ['JSON_CONTAINS' => JsonContains::class]],
                ],
            ],
        ],
    ],
]);
