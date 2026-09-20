<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Filter;

use ApiPlatform\Doctrine\Orm\Util\QueryNameGenerator;
use ApiPlatform\Metadata\Exception\InvalidArgumentException;
use ApiPlatform\Metadata\GetCollection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use DR\Review\ApiPlatform\Filter\CommentFilepathFilter;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentFilepathFilter::class)]
class CommentFilepathFilterTest extends AbstractTestCase
{
    private CommentFilepathFilter $filter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->filter = new CommentFilepathFilter();
    }

    public function testAbsentParameterDoesNothing(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);

        $this->filter->apply($queryBuilder, new QueryNameGenerator(), Comment::class, new GetCollection(), ['filters' => []]);

        static::assertSame('SELECT c FROM DR\\Review\\Entity\\Review\\Comment c', $queryBuilder->getDQL());
        static::assertCount(0, $queryBuilder->getParameters());
    }

    public function testExactFilepathUsesEquality(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);

        $this->filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            Comment::class,
            new GetCollection(),
            ['filters' => ['exact' => ['filepath' => 'src/Foo.php']]],
        );

        static::assertSame('SELECT c FROM DR\\Review\\Entity\\Review\\Comment c WHERE c.filePath = :filepath_p1', $queryBuilder->getDQL());
        static::assertStringNotContainsString('LIKE', $queryBuilder->getDQL());
        static::assertSame('src/Foo.php', $queryBuilder->getParameter('filepath_p1')->getValue());
    }

    public function testNonScalarFilepathIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->filter->apply(
            $this->createQueryBuilder(Comment::class),
            new QueryNameGenerator(),
            Comment::class,
            new GetCollection(),
            ['filters' => ['exact' => ['filepath' => ['src/Foo.php']]]],
        );
    }

    public function testUnsupportedExactPropertyDoesNothing(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);

        $this->filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            Comment::class,
            new GetCollection(),
            ['filters' => ['exact' => ['message' => 'Comment text']]],
        );

        static::assertSame('SELECT c FROM DR\\Review\\Entity\\Review\\Comment c', $queryBuilder->getDQL());
    }

    public function testFilepathOrderUsesStandardDirection(): void
    {
        $queryBuilder = $this->createQueryBuilder(Comment::class);

        $this->filter->apply(
            $queryBuilder,
            new QueryNameGenerator(),
            Comment::class,
            new GetCollection(),
            ['filters' => ['order' => ['filepath' => 'desc']]],
        );

        static::assertSame('SELECT c FROM DR\\Review\\Entity\\Review\\Comment c ORDER BY c.filePath DESC', $queryBuilder->getDQL());
    }

    public function testDescriptionDocumentsParameters(): void
    {
        $description = $this->filter->getDescription(Comment::class);

        static::assertArrayHasKey('exact[filepath]', $description);
        static::assertArrayHasKey('order[filepath]', $description);
        static::assertSame([], $this->filter->getDescription(User::class));
    }

    private function createQueryBuilder(string $resourceClass): QueryBuilder
    {
        $entityManager = $this->createStub(EntityManagerInterface::class);

        return new QueryBuilder($entityManager)->select('c')->from($resourceClass, 'c');
    }
}
