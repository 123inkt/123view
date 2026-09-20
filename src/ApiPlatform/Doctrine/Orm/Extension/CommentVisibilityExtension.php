<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Doctrine\Orm\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Service\User\UserEntityProvider;

class CommentVisibilityExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private readonly UserEntityProvider $userProvider)
    {
    }

    /**
     * @SuppressWarnings(UnusedFormalParameter)
     * @param class-string $resourceClass
     * @param array<string, mixed> $context
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== Comment::class) {
            return;
        }

        $alias             = $queryBuilder->getRootAliases()[0];
        $typeParameterName = $queryNameGenerator->generateParameterName('comment_type');
        $userParameterName = $queryNameGenerator->generateParameterName('comment_user');
        // Passing the OR expression as a string makes Doctrine wrap it before
        // combining it with later filter predicates.
        $visibleComments = sprintf(
            '%s.type = :%s OR %s.user = :%s',
            $alias,
            $typeParameterName,
            $alias,
            $userParameterName,
        );

        $queryBuilder
            ->andWhere($visibleComments)
            ->setParameter($typeParameterName, CommentTypeEnum::Final)
            ->setParameter($userParameterName, $this->userProvider->getCurrentUser());
    }
}
