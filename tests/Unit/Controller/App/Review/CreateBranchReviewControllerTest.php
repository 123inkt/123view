<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\CreateBranchReviewController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Doctrine\Type\CodeReviewType;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\ReviewCreated;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Service\CodeReview\CodeReviewCreationService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use stdClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

#[CoversClass(CreateBranchReviewController::class)]
class CreateBranchReviewControllerTest extends AbstractControllerTestCase
{
    private CodeReviewCreationService&MockObject $reviewCreationService;
    private CodeReviewRevisionService&MockObject $revisionService;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private MessageBusInterface&MockObject       $messageBus;
    private Envelope                             $envelope;

    protected function setUp(): void
    {
        $this->envelope              = new Envelope(new stdClass(), []);
        $this->reviewCreationService = $this->createMock(CodeReviewCreationService::class);
        $this->revisionService       = $this->createMock(CodeReviewRevisionService::class);
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->messageBus            = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeBranchIsRequired(): void
    {
        $repository = new Repository();
        $request    = new Request();

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Branch request property is mandatory');
        ($this->controller)($request, $repository);
    }

    public function testInvokeReviewShouldNotExist(): void
    {
        $repository = new Repository();
        $request    = new Request(request: ['branch' => 'branch']);
        $review     = new CodeReview();

        $this->reviewRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => 'branch'])
            ->willReturn($review);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('A branch review already exists');
        ($this->controller)($request, $repository);
    }

    public function testInvoke(): void
    {
        $repository = new Repository();
        $request    = new Request(request: ['branch' => 'branch']);
        $review     = new CodeReview();
        $review->setId(123);
        $revision = new Revision();
        $revision->setId(456);

        $this->expectGetUser((new User())->setId(789));
        $this->reviewRepository->expects(self::once())
            ->method('findOneBy')
            ->with(['repository' => $repository, 'type' => CodeReviewType::BRANCH, 'referenceId' => 'branch'])
            ->willReturn(null);
        $this->reviewCreationService->expects(self::once())->method('createFromBranch')->with($repository, 'branch')->willReturn($review);
        $this->revisionService->expects(self::once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->reviewRepository->expects(self::once())->method('save')->with($review, true);
        $this->messageBus->expects(self::once())->method('dispatch')->with(new ReviewCreated(123, 456, 789))->willReturn($this->envelope);
        $this->expectRedirectToRoute(ReviewController::class, ['review' => $review])->willReturn('url');

        ($this->controller)($request, $repository);
    }

    public function getController(): AbstractController
    {
        return new CreateBranchReviewController($this->reviewCreationService, $this->revisionService, $this->reviewRepository, $this->messageBus);
    }
}
