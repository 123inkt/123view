<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Factory\CodeReviewOutputFactory;
use DR\Review\ApiPlatform\Factory\UserOutputFactory;
use DR\Review\ApiPlatform\Output\UserOutput;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewer;
use DR\Review\Entity\User\User;
use DR\Review\Service\CodeReview\CodeReviewerStateResolver;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

#[CoversClass(CodeReviewOutputFactory::class)]
class CodeReviewOutputFactoryTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject     $urlGenerator;
    private UserOutputFactory&MockObject         $userOutputFactory;
    private CodeReviewerStateResolver&MockObject $reviewerStateResolver;
    private CodeReviewOutputFactory              $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator          = $this->createMock(UrlGeneratorInterface::class);
        $this->userOutputFactory     = $this->createMock(UserOutputFactory::class);
        $this->reviewerStateResolver = $this->createMock(CodeReviewerStateResolver::class);
        $this->factory               = new CodeReviewOutputFactory($this->urlGenerator, $this->userOutputFactory, $this->reviewerStateResolver);
    }

    public function testCreate(): void
    {
        // setup dependencies
        $userA      = (new User())->setId(123)->setName('name A')->setEmail('email A');
        $userB      = (new User())->setId(234)->setName('name B')->setEmail('email B');
        $reviewer   = (new CodeReviewer())->setUser($userA);
        $repository = (new Repository())->setId(789);

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

        $this->userOutputFactory->expects($this->exactly(2))
            ->method('create')
            ->with(...consecutive([$userA], [$userB]))
            ->willReturn(static::createStub(UserOutput::class));
        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review], UrlGenerator::ABSOLUTE_URL)
            ->willReturn('url');
        $this->reviewerStateResolver->expects($this->once())->method('getReviewersState')->with($review)->willReturn(CodeReviewerStateType::OPEN);

        $output = $this->factory->create($review, [$reviewer], [$userB]);

        static::assertSame(456, $output->id);
        static::assertSame(789, $output->repositoryId);
        static::assertSame('cr-951', $output->slug);
        static::assertSame('title', $output->title);
        static::assertSame('description', $output->description);
        static::assertSame('url', $output->url);
        static::assertSame('open', $output->state);
        static::assertSame(CodeReviewerStateType::OPEN, $output->reviewerState);
        static::assertNotNull($output->reviewers);
        static::assertCount(1, $output->reviewers);
        static::assertNotNull($output->authors);
        static::assertCount(1, $output->authors);
        static::assertSame(1000, $output->createTimestamp);
        static::assertSame(2000, $output->updateTimestamp);
    }
}
