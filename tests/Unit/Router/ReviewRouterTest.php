<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Router;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Router\ReviewRouter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Router\ReviewRouter
 * @covers ::__construct
 */
class ReviewRouterTest extends AbstractTestCase
{
    private RouterInterface&MockObject $router;
    private ReviewRouter               $reviewRouter;

    public function setUp(): void
    {
        parent::setUp();
        $this->router       = $this->createMock(RouterInterface::class);
        $this->reviewRouter = new ReviewRouter($this->router);
    }

    /**
     * @covers ::setContext
     */
    public function testSetContext(): void
    {
        $context = new RequestContext();
        $this->router->expects(self::once())->method('setContext')->with($context);
        $this->reviewRouter->setContext($context);
    }

    /**
     * @covers ::getContext
     */
    public function testGetContext(): void
    {
        $context = new RequestContext();
        $this->router->expects(self::once())->method('getContext')->willReturn($context);
        static::assertSame($context, $this->reviewRouter->getContext());
    }

    /**
     * @covers ::getRouteCollection
     */
    public function testGetRouteCollection(): void
    {
        $collection = new RouteCollection();
        $this->router->expects(self::once())->method('getRouteCollection')->willReturn($collection);
        static::assertSame($collection, $this->reviewRouter->getRouteCollection());
    }

    /**
     * @covers ::generate
     */
    public function testGenerateReviewControllerRequiresReviewProperty(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Missing or invalid `review` in route parameters for ReviewController');
        $this->reviewRouter->generate(ReviewController::class);
    }

    /**
     * @covers ::generate
     */
    public function testGenerateReviewControllerRequiresCodeReview(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Missing or invalid `review` in route parameters for ReviewController');
        $this->reviewRouter->generate(ReviewController::class, ['review' => new stdClass()]);
    }

    /**
     * @covers ::generate
     */
    public function testGenerateReviewControllerGenerateReviewRoute(): void
    {
        $repository = new Repository();
        $repository->setName('repository');
        $review = new CodeReview();
        $review->setProjectId(123);
        $review->setRepository($repository);

        $this->router->expects(self::once())->method('generate')
            ->with(ReviewController::class, ['repositoryName' => 'repository', 'reviewId' => 123])
            ->willReturn('url');

        $actualUrl = $this->reviewRouter->generate(ReviewController::class, ['review' => $review]);
        static::assertSame('url', $actualUrl);
    }

    /**
     * @covers ::generate
     */
    public function testGenerateReviewController(): void
    {
        $params        = ['foo' => 'bar'];
        $referenceType = UrlGeneratorInterface::RELATIVE_PATH;

        $this->router->expects(self::once())->method('generate')
            ->with('route', $params, $referenceType)
            ->willReturn('url');

        $actualUrl = $this->reviewRouter->generate('route', $params, $referenceType);
        static::assertSame('url', $actualUrl);
    }

    /**
     * @covers ::match
     */
    public function testMatch(): void
    {
        $pathinfo = 'pathinfo';
        $result   = ['foo' => 'bar'];
        $this->router->expects(self::once())->method('match')->with($pathinfo)->willReturn($result);
        static::assertSame($result, $this->reviewRouter->match($pathinfo));
    }
}
