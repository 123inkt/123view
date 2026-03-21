<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Router;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Router\ReviewRouter;
use DR\Review\Tests\AbstractKernelTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\Routing\RouterInterface;

#[CoversNothing]
class ReviewRouterTest extends AbstractKernelTestCase
{
    /**
     * @throws Exception
     */
    public function testGenerate(): void
    {
        $repository = new Repository();
        $repository->setName('repository');
        $review = new CodeReview();
        $review->setProjectId(123);
        $review->setRepository($repository);

        /** @var RouterInterface $router */
        $router = self::getContainer()->get(ReviewRouter::class);

        $actualUrl = $router->generate(ReviewController::class, ['review' => $review]);
        static::assertSame('/app/repository/review/cr-123', $actualUrl);
    }
}
