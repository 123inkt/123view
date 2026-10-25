<?php

declare(strict_types=1);

namespace DR\Review\ApiPlatform\Extension;

use ApiPlatform\Doctrine\Orm\Extension\QueryCollectionExtensionInterface;
use ApiPlatform\Doctrine\Orm\Util\QueryBuilderHelper;
use ApiPlatform\Doctrine\Orm\Util\QueryNameGeneratorInterface;
use ApiPlatform\Metadata\Operation;
use Doctrine\ORM\QueryBuilder;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Service\User\UserEntityProvider;

readonly class CommentReplyVisibilityExtension implements QueryCollectionExtensionInterface
{
    public function __construct(private UserEntityProvider $userProvider)
    {
    }

    /**
     * @inheritDoc
     *
     * @param class-string         $resourceClass
     * @param array<string, mixed> $context
     */
    public function applyToCollection(
        QueryBuilder $queryBuilder,
        QueryNameGeneratorInterface $queryNameGenerator,
        string $resourceClass,
        ?Operation $operation = null,
        array $context = [],
    ): void {
        if ($resourceClass !== CommentReply::class) {
            return;
        }

        $replyAlias   = $queryBuilder->getRootAliases()[0];
        $commentAlias = QueryBuilderHelper::addJoinOnce($queryBuilder, $queryNameGenerator, $replyAlias, 'comment');
        $finalType    = $queryNameGenerator->generateParameterName('comment_type_final');
        $draftType    = $queryNameGenerator->generateParameterName('comment_type_draft');
        $user         = $queryNameGenerator->generateParameterName('comment_user');

        $visibleReplies = sprintf(
            '%s.type = :%s OR (%s.type = :%s AND %s.user = :%s)',
            $commentAlias,
            $finalType,
            $commentAlias,
            $draftType,
            $commentAlias,
            $user,
        );

        $queryBuilder
            ->andWhere($visibleReplies)
            ->setParameter($finalType, CommentTypeEnum::Final)
            ->setParameter($draftType, CommentTypeEnum::Draft)
            ->setParameter($user, $this->userProvider->getCurrentUser());
    }
}
