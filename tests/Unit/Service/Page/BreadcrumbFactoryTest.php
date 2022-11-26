<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Page;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Controller\App\Review\ReviewsController;
use DR\GitCommitNotification\Entity\Config\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Service\Page\BreadcrumbFactory;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Page\BreadcrumbFactory
 * @covers ::__construct
 */
class BreadcrumbFactoryTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private BreadcrumbFactory                $factory;

    public function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->factory      = new BreadcrumbFactory($this->urlGenerator);
    }

    /**
     * @covers ::createForReview
     */
    public function testCreateForReview(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('foobar');

        $review = new CodeReview();
        $review->setId(789);
        $review->setProjectId(456);
        $review->setRepository($repository);

        $this->urlGenerator->expects(self::exactly(2))->method('generate')
            ->withConsecutive(
                [ReviewsController::class, ['id' => 123]],
                [ReviewController::class, ['review' => $review]]
            )
            ->willReturn('urlA', 'urlB');

        $crumbs = $this->factory->createForReview($review);
        static::assertCount(2, $crumbs);
        static::assertSame('Foobar', $crumbs[0]->label);
        static::assertSame('urlA', $crumbs[0]->url);

        static::assertSame('CR-456', $crumbs[1]->label);
        static::assertSame('urlB', $crumbs[1]->url);
    }

    /**
     * @covers ::createForReviews
     */
    public function testCreateForReviews(): void
    {
        $repository = new Repository();
        $repository->setId(123);
        $repository->setDisplayName('foobar');

        $this->urlGenerator->expects(self::once())->method('generate')->with(ReviewsController::class, ['id' => 123])->willReturn('url');

        $crumbs = $this->factory->createForReviews($repository);
        static::assertCount(1, $crumbs);
        static::assertSame('Foobar', $crumbs[0]->label);
        static::assertSame('url', $crumbs[0]->url);
    }
}
