<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Router;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Router\ReviewRouter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RouteCollection;

#[CoversClass(ReviewRouter::class)]
class ReviewRouterTest extends AbstractTestCase
{
    private Router&MockObject $router;
    private ReviewRouter      $reviewRouter;

    public function setUp(): void
    {
        parent::setUp();
        $this->router       = $this->createMock(Router::class);
        $this->reviewRouter = new ReviewRouter($this->router);
    }

    public function testSetContext(): void
    {
        $context = new RequestContext();
        $this->router->expects($this->once())->method('setContext')->with($context);
        $this->reviewRouter->setContext($context);
    }

    public function testGetContext(): void
    {
        $context = new RequestContext();
        $this->router->expects($this->once())->method('getContext')->willReturn($context);
        static::assertSame($context, $this->reviewRouter->getContext());
    }

    public function testGetRouteCollection(): void
    {
        $collection = new RouteCollection();
        $this->router->expects($this->once())->method('getRouteCollection')->willReturn($collection);
        static::assertSame($collection, $this->reviewRouter->getRouteCollection());
    }

    public function testGenerateReviewControllerRequiresReviewProperty(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Missing or invalid `review` in route parameters for ReviewController');
        $this->reviewRouter->generate(ReviewController::class);
    }

    public function testGenerateReviewControllerRequiresCodeReview(): void
    {
        $this->expectException(InvalidParameterException::class);
        $this->expectExceptionMessage('Missing or invalid `review` in route parameters for ReviewController');
        $this->reviewRouter->generate(ReviewController::class, ['review' => new stdClass()]);
    }

    public function testGenerateReviewControllerGenerateReviewRoute(): void
    {
        $repository = new Repository();
        $repository->setName('repository');
        $review = new CodeReview();
        $review->setProjectId(123);
        $review->setRepository($repository);

        $this->router->expects($this->once())->method('generate')
            ->with(ReviewController::class, ['repositoryName' => 'repository', 'reviewId' => 123])
            ->willReturn('url');

        $actualUrl = $this->reviewRouter->generate(ReviewController::class, ['review' => $review]);
        static::assertSame('url', $actualUrl);
    }

    public function testGenerateReviewController(): void
    {
        $params        = ['foo' => 'bar'];
        $referenceType = UrlGeneratorInterface::RELATIVE_PATH;

        $this->router->expects($this->once())->method('generate')
            ->with('route', $params, $referenceType)
            ->willReturn('url');

        $actualUrl = $this->reviewRouter->generate('route', $params, $referenceType);
        static::assertSame('url', $actualUrl);
    }

    public function testMatch(): void
    {
        $pathinfo = 'pathinfo';
        $result   = ['foo' => 'bar'];
        $this->router->expects($this->once())->method('match')->with($pathinfo)->willReturn($result);
        static::assertSame($result, $this->reviewRouter->match($pathinfo));
    }

    public function testMatchRequest(): void
    {
        $request = new Request();
        $result  = ['foo' => 'bar'];
        $this->router->expects($this->once())->method('matchRequest')->with($request)->willReturn($result);
        static::assertSame($result, $this->reviewRouter->matchRequest($request));
    }

    public function testWarmUp(): void
    {
        $result = ['foo' => 'bar'];
        $this->router->expects($this->once())->method('warmUp')->with('cache-dir')->willReturn($result);
        static::assertSame($result, $this->reviewRouter->warmUp('cache-dir'));
    }
}
