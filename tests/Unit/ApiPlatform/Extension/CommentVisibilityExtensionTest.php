<?php

declare(strict_types=1);

namespace ApiPlatform\Extension;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use DR\Review\ApiPlatform\Extension\CommentVisibilityExtension;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\User\User;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CommentVisibilityExtension::class)]
class CommentVisibilityExtensionTest extends AbstractTestCase
{
    private UserEntityProvider&MockObject $userProvider;
    private CommentVisibilityExtension   $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userProvider = $this->createMock(UserEntityProvider::class);
        $this->extension   = new CommentVisibilityExtension($this->userProvider);
    }

    public function testNonCommentResourcesAreUnchanged(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);

        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->extension->applyToCollection($queryBuilder, new QueryNameGenerator(), User::class);

        static::assertSame('SELECT c FROM DR\\Review\\Entity\\Review\\Comment c', $queryBuilder->getDQL());
        static::assertCount(0, $queryBuilder->getParameters());
    }

    public function testAppliesVisibilityPredicate(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class)
            ->andWhere('c.id = :existing')
            ->setParameter('existing', 123);
        $user = new User()->setId(10);

        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->extension->applyToCollection($queryBuilder, new QueryNameGenerator(), Comment::class);
        static::assertSame(
            'SELECT c FROM DR\\Review\\Entity\\Review\\Comment c WHERE c.id = :existing AND (c.type = :comment_type_p1 OR c.user = :comment_user_p2)',
            $queryBuilder->getDQL(),
        );
        static::assertSame(123, $queryBuilder->getParameter('existing')->getValue());
        static::assertSame(CommentTypeEnum::Final, $queryBuilder->getParameter('comment_type_p1')->getValue());
        static::assertSame($user, $queryBuilder->getParameter('comment_user_p2')->getValue());
    }

    public function testGeneratedNamesAvoidCollisions(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);
        $queryNameGenerator = new QueryNameGenerator();
        $existingParameter = $queryNameGenerator->generateParameterName('comment_type');
        $queryBuilder->setParameter($existingParameter, 'existing');
        $user = new User()->setId(10);

        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->extension->applyToCollection($queryBuilder, $queryNameGenerator, Comment::class);

        static::assertSame('existing', $queryBuilder->getParameter('comment_type_p1')->getValue());
        static::assertSame(CommentTypeEnum::Final, $queryBuilder->getParameter('comment_type_p2')->getValue());
        static::assertSame($user, $queryBuilder->getParameter('comment_user_p3')->getValue());
    }

    private function createQueryBuilder(string $resourceClass): QueryBuilder
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        return new QueryBuilder($entityManager)->select('c')->from($resourceClass, 'c');
    }
}
