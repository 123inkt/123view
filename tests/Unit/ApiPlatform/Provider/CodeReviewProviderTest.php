<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Service\User\UserService;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGenerator;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\Provider\CodeReviewProvider
 * @covers ::__construct
 */
class CodeReviewProviderTest extends AbstractTestCase
{
    /** @var MockObject&ProviderInterface<CodeReview> */
    private ProviderInterface&MockObject     $collectionProvider;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private UserService&MockObject           $userService;
    private CodeReviewProvider               $reviewProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->urlGenerator       = $this->createMock(UrlGeneratorInterface::class);
        $this->userService        = $this->createMock(UserService::class);
        $this->reviewProvider     = new CodeReviewProvider($this->collectionProvider, $this->urlGenerator, $this->userService);
    }

    /**
     * @covers ::provide
     */
    public function testProvideShouldOnlySupportGetCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->reviewProvider->provide(new Get());
    }

    /**
     * @covers ::provide
     */
    public function testProvide(): void
    {
        $operation = new GetCollection();

        // setup dependencies
        $userA      = (new User())->setId(123)->setName('name A')->setEmail('email A');
        $userB      = (new User())->setId(234)->setName('name B')->setEmail('email B');
        $reviewer   = (new CodeReviewer())->setUser($userA);
        $repository = (new Repository())->setId(789);
        $revision   = new Revision();

        // setup review
        $review = new CodeReview();
        $review->setId(456);
        $review->setRepository($repository);
        $review->setProjectId(951);
        $review->setTitle('title');
        $review->setDescription('description');
        $review->setState('open');
        $review->setCreateTimestamp(1000);
        $review->setUpdateTimestamp(2000);
        $review->getReviewers()->add($reviewer);
        $review->getRevisions()->add($revision);

        // setup mocks
        $this->collectionProvider->expects(self::once())->method('provide')->with($operation)->willReturn(new ArrayIterator([$review]));
        $this->userService->expects(self::once())->method('getUsersForRevisions')->with([$revision])->willReturn([$userB]);
        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL)
            ->willReturn('url');

        // execute test
        $result = $this->reviewProvider->provide(new GetCollection());

        // assert
        static::assertCount(1, $result);
        static::assertEquals(
            new CodeReviewOutput(
                456,
                789,
                'cr-951',
                'title',
                'description',
                'url',
                'open',
                'open',
                [new UserOutput(234, 'name B', 'email B')],
                [new UserOutput(123, 'name A', 'email A')],
                1000,
                2000
            ),
            $result[0]
        );
    }
}
