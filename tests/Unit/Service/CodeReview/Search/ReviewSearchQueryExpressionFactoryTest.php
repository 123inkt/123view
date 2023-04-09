<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Search;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Expr;
use DR\Review\Entity\User\User;
use DR\Review\QueryParser\Term\Match\MatchFilter;
use DR\Review\QueryParser\Term\Match\MatchWord;
use DR\Review\Service\CodeReview\Search\ReviewSearchQueryExpressionFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ReviewSearchQueryExpressionFactory::class)]
class ReviewSearchQueryExpressionFactoryTest extends AbstractTestCase
{
    private User                               $user;
    private ReviewSearchQueryExpressionFactory $expressionFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user              = new User();
        $this->expressionFactory = new ReviewSearchQueryExpressionFactory($this->user);
    }

    public function testCreateReviewIdExpressionShouldNotMatch(): void
    {
        static::assertNull($this->expressionFactory->createReviewIdExpression(new MatchWord('query'), new ArrayCollection()));
        static::assertNull($this->expressionFactory->createReviewIdExpression(new MatchFilter('foo', 'bar'), new ArrayCollection()));
    }

    public function testCreateReviewIdExpressionShouldMatch(): void
    {
        $collection = new ArrayCollection();

        $expression = $this->expressionFactory->createReviewIdExpression(new MatchFilter('id', '123'), $collection);

        static::assertEquals(new Expr\Comparison('r.projectId', '=', ':projectId1'), $expression);
        static::assertSame(['projectId1' => '123'], $collection->toArray());
    }

    public function testCreateReviewStateExpressionShouldNotMatch(): void
    {
        static::assertNull($this->expressionFactory->createReviewStateExpression(new MatchWord('query'), new ArrayCollection()));
        static::assertNull($this->expressionFactory->createReviewStateExpression(new MatchFilter('foo', 'bar'), new ArrayCollection()));
    }

    public function testCreateReviewStateExpressionShouldMatch(): void
    {
        $collection = new ArrayCollection();

        $expression = $this->expressionFactory->createReviewStateExpression(new MatchFilter('state', 'open'), $collection);

        static::assertEquals(new Expr\Comparison('r.state', '=', ':state1'), $expression);
        static::assertSame(['state1' => 'open'], $collection->toArray());
    }

    public function testCreateReviewAuthorExpressionShouldNotMatch(): void
    {
        static::assertNull($this->expressionFactory->createReviewAuthorExpression(new MatchWord('query'), new ArrayCollection()));
        static::assertNull($this->expressionFactory->createReviewAuthorExpression(new MatchFilter('foo', 'bar'), new ArrayCollection()));
    }

    public function testCreateReviewAuthorExpressionWithMe(): void
    {
        $this->user->setEmail('email');
        $collection = new ArrayCollection();

        $expression = $this->expressionFactory->createReviewAuthorExpression(new MatchFilter('author', 'me'), $collection);

        static::assertEquals(new Expr\Comparison('rv.authorEmail', '=', ':authorEmail1'), $expression);
        static::assertSame(['authorEmail1' => 'email'], $collection->toArray());
    }

    public function testCreateReviewAuthorExpressionWithUser(): void
    {
        $collection = new ArrayCollection();

        $expression = $this->expressionFactory->createReviewAuthorExpression(new MatchFilter('author', 'sherlock'), $collection);

        static::assertEquals(
            new Expr\Orx(
                [
                    new Expr\Comparison('rv.authorEmail', 'LIKE', ':authorEmail1'),
                    new Expr\Comparison('rv.authorName', 'LIKE', ':authorEmail1')
                ]
            ),
            $expression
        );
        static::assertSame(['authorEmail1' => '%sherlock%'], $collection->toArray());
    }

    public function testCreateReviewReviewerExpressionShouldNotMatch(): void
    {
        static::assertNull($this->expressionFactory->createReviewReviewerExpression(new MatchWord('query'), new ArrayCollection()));
        static::assertNull($this->expressionFactory->createReviewReviewerExpression(new MatchFilter('foo', 'bar'), new ArrayCollection()));
    }

    public function testCreateReviewReviewerExpressionWithMe(): void
    {
        $collection = new ArrayCollection();
        $expression = $this->expressionFactory->createReviewReviewerExpression(new MatchFilter('reviewer', 'me'), $collection);

        $this->user->setEmail('email');
        static::assertEquals(new Expr\Comparison('u.email', '=', ':reviewerEmail1'), $expression);
        static::assertSame(['reviewerEmail1' => 'email'], $collection->toArray());
    }

    public function testCreateReviewReviewerExpressionWithUser(): void
    {
        $collection = new ArrayCollection();
        $expression = $this->expressionFactory->createReviewReviewerExpression(new MatchFilter('reviewer', 'sherlock'), $collection);

        static::assertEquals(
            new Expr\Orx(
                [
                    new Expr\Comparison('u.email', 'LIKE', ':reviewerEmail1'),
                    new Expr\Comparison('u.name', 'LIKE', ':reviewerEmail1')
                ]
            ),
            $expression
        );
        static::assertSame(['reviewerEmail1' => '%sherlock%'], $collection->toArray());
    }

    public function testCreateSearchExpression(): void
    {
    }
}
