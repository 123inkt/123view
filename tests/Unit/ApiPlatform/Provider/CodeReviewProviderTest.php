<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\State\ProviderInterface;
use ArrayIterator;
use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Output\CodeReviewOutput;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Service\User\UserService;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(CodeReviewProvider::class)]
class CodeReviewProviderTest extends AbstractTestCase
{
    /** @var MockObject&ProviderInterface<CodeReview> */
    private ProviderInterface&MockObject       $collectionProvider;
    private CodeReviewOutputFactory&MockObject $reviewOutputFactory;
    private UserService&MockObject             $userService;
    private CodeReviewProvider                 $reviewProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider  = $this->createMock(ProviderInterface::class);
        $this->reviewOutputFactory = $this->createMock(CodeReviewOutputFactory::class);
        $this->userService         = $this->createMock(UserService::class);
        $this->reviewProvider      = new CodeReviewProvider($this->collectionProvider, $this->reviewOutputFactory, $this->userService);
    }

    public function testProvideShouldOnlySupportGetCollection(): void
    {
        $this->collectionProvider->expects($this->never())->method('provide');
        $this->reviewOutputFactory->expects($this->never())->method('create');
        $this->userService->expects($this->never())->method('getUsersForRevisions');
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->reviewProvider->provide(new Get());
    }

    public function testProvide(): void
    {
        $user      = new User();
        $operation = new GetCollection();
        $revision  = new Revision();
        $reviewer  = new CodeReviewer();
        $review    = new CodeReview();
        $review->getReviewers()->add($reviewer);
        $review->getRevisions()->add($revision);

        $output = static::createStub(CodeReviewOutput::class);

        // setup mocks
        $this->collectionProvider->expects($this->once())->method('provide')->with($operation)->willReturn(new ArrayIterator([$review]));
        $this->userService->expects($this->once())->method('getUsersForRevisions')->with([$revision])->willReturn([$user]);
        $this->reviewOutputFactory->expects($this->once())
            ->method('create')
            ->with($review, [$reviewer], [$user])
            ->willReturn($output);

        // execute test
        $result = $this->reviewProvider->provide(new GetCollection());
        static::assertSame([$output], $result);
    }
}
