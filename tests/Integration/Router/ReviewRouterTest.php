<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Integration\Router;

use DR\GitCommitNotification\Controller\App\Review\ReviewController;
use DR\GitCommitNotification\Entity\Repository\Repository;
use DR\GitCommitNotification\Entity\Review\CodeReview;
use DR\GitCommitNotification\Tests\AbstractKernelTestCase;
use Exception;
use Symfony\Component\Routing\RouterInterface;

/**
 * @coversNothing
 */
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
        $router = self::getContainer()->get('router');

        $actualUrl = $router->generate(ReviewController::class, ['review' => $review]);
        static::assertSame('/app/repository/review/cr-123', $actualUrl);
    }
}
