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
use Symfony\Config\DoctrineConfig;

return static function (DoctrineConfig $config): void {
    $dbal = $config->dbal();
    $orm  = $config->orm();

    $dbal->connection('default')
        ->url('%env(resolve:DATABASE_URL)%')
        ->serverVersion('%env(MYSQL_VERSION)%')
        ->mappingType('enum', 'string');

    $dbal->type(AuthenticationType::TYPE)->class(AuthenticationType::class);
    $dbal->type(CodeReviewStateType::TYPE)->class(CodeReviewStateType::class);
    $dbal->type(CodeReviewType::TYPE)->class(CodeReviewType::class);
    $dbal->type(CodeReviewerStateType::TYPE)->class(CodeReviewerStateType::class);
    $dbal->type(ColorThemeType::TYPE)->class(ColorThemeType::class);
    $dbal->type(CommentStateType::TYPE)->class(CommentStateType::class);
    $dbal->type(CommentTagType::TYPE)->class(CommentTagType::class);
    $dbal->type(DiffAlgorithmType::TYPE)->class(DiffAlgorithmType::class);
    $dbal->type(FilterType::TYPE)->class(FilterType::class);
    $dbal->type(FrequencyType::TYPE)->class(FrequencyType::class);
    $dbal->type(LineCoverageType::TYPE)->class(LineCoverageType::class);
    $dbal->type(MailThemeType::TYPE)->class(MailThemeType::class);
    $dbal->type(NotificationSendType::TYPE)->class(NotificationSendType::class);
    $dbal->type(NotificationStatusType::TYPE)->class(NotificationStatusType::class);
    $dbal->type(RepositoryGitType::TYPE)->class(RepositoryGitType::class);
    $dbal->type(SpaceSeparatedStringValueType::TYPE)->class(SpaceSeparatedStringValueType::class);
    $dbal->type(UriType::TYPE)->class(UriType::class);

    $orm->defaultEntityManager('default');
    $orm->controllerResolver()->autoMapping(false);

    $em = $orm->entityManager('default');
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
