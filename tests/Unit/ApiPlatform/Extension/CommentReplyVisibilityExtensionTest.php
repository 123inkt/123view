<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use DR\Review\ApiPlatform\Extension\CommentReplyVisibilityExtension;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentReplyVisibilityExtension::class)]
class CommentReplyVisibilityExtensionTest extends AbstractTestCase
{
    private UserEntityProvider&MockObject $userProvider;
    private CommentReplyVisibilityExtension $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userProvider = $this->createMock(UserEntityProvider::class);
        $this->extension    = new CommentReplyVisibilityExtension($this->userProvider);
    }

    public function testNonReplyResourcesAreUnchanged(): void
    {
        $queryBuilder = $this->createQueryBuilder();

        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->extension->applyToCollection($queryBuilder, new QueryNameGenerator(), User::class);

        static::assertSame('SELECT r FROM DR\\Review\\Entity\\Review\\CommentReply r', $queryBuilder->getDQL());
        static::assertCount(0, $queryBuilder->getParameters());
    }

    public function testAppliesParentVisibilityPredicate(): void
    {
        $queryBuilder = $this->createQueryBuilder()->andWhere('r.id = :existing')->setParameter('existing', 123);
        $user         = new User()->setId(10);

        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->extension->applyToCollection($queryBuilder, new QueryNameGenerator(), CommentReply::class, new GetCollection());

        $expectedDql = 'SELECT r FROM DR\\Review\\Entity\\Review\\CommentReply r INNER JOIN r.comment comment_a1 '
            . 'WHERE r.id = :existing AND (comment_a1.type = :comment_type_final_p1 '
            . 'OR (comment_a1.type = :comment_type_draft_p2 AND comment_a1.user = :comment_user_p3))';

        static::assertSame($expectedDql, $queryBuilder->getDQL());
        static::assertSame(123, $queryBuilder->getParameter('existing')?->getValue());
        static::assertSame(CommentTypeEnum::Final, $queryBuilder->getParameter('comment_type_final_p1')?->getValue());
        static::assertSame(CommentTypeEnum::Draft, $queryBuilder->getParameter('comment_type_draft_p2')?->getValue());
        static::assertSame($user, $queryBuilder->getParameter('comment_user_p3')?->getValue());
    }

    private function createQueryBuilder(): QueryBuilder
    {
        $entityManager = static::createStub(EntityManagerInterface::class);

        return new QueryBuilder($entityManager)->select('r')->from(CommentReply::class, 'r');
    }
}
