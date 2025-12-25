<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\AiCodeReviewController;
use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Message\Review\AiReviewRequested;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Messenger\MessageBusInterface;

/**
 * @extends AbstractControllerTestCase<AiCodeReviewController>
 */
#[CoversClass(AiCodeReviewController::class)]
class AiCodeReviewControllerTest extends AbstractControllerTestCase
{
    private EntityManagerInterface&MockObject $doctrine;
    private MessageBusInterface&MockObject    $messageBus;

    protected function setUp(): void
    {
        $this->doctrine   = $this->createMock(EntityManagerInterface::class);
        $this->messageBus = $this->createMock(MessageBusInterface::class);
        parent::setUp();
    }

    public function testInvokeHappyFlow(): void
    {
        $aiUser      = (new User())->setId(100);
        $regularUser = (new User())->setId(200);
        $currentUser = (new User())->setId(300);

        $aiComment      = (new Comment())->setUser($aiUser);
        $regularComment = (new Comment())->setUser($regularUser);

        $review = (new CodeReview())->setId(123);
        $review->getComments()->add($aiComment);
        $review->getComments()->add($regularComment);

        $this->doctrine->expects($this->once())->method('remove')->with($aiComment);
        $this->doctrine->expects($this->once())->method('persist')->with($review);
        $this->doctrine->expects($this->once())->method('flush');

        $this->expectGetUser($currentUser);
        $this->messageBus->expects($this->once())->method('dispatch')->with(new AiReviewRequested(123, 300))->willReturn($this->envelope);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);
        $this->expectAddFlash('success', 'ai.review.requested');

        ($this->controller)($review);

        static::assertTrue($review->isAiReviewRequested());
        static::assertCount(1, $review->getComments());
        static::assertSame($regularComment, $review->getComments()->first());
    }

    public function testInvokeDebugModeDoesNotSetFlag(): void
    {
        $currentUser = (new User())->setId(300);
        $review      = (new CodeReview())->setId(123);

        $this->doctrine->expects($this->once())->method('persist')->with($review);
        $this->doctrine->expects($this->once())->method('flush');

        $this->expectGetUser($currentUser);
        $this->messageBus->expects($this->once())->method('dispatch')->with(new AiReviewRequested(123, 300))->willReturn($this->envelope);

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);
        $this->expectAddFlash('success', 'ai.review.requested');

        ($this->getControllerWithDebug())($review);

        static::assertFalse($review->isAiReviewRequested());
    }

    public function testInvokeReviewAlreadyRequestedReturnsWarning(): void
    {
        $review = (new CodeReview())->setId(123)->setAiReviewRequested(true);

        $this->messageBus->expects($this->never())->method('dispatch');
        $this->doctrine->expects($this->never())->method('persist');

        $this->expectRefererRedirect(ReviewController::class, ['review' => $review]);
        $this->expectAddFlash('warning', 'ai.review.already.requested');

        ($this->controller)($review);
    }

    public function getController(): AbstractController
    {
        return new AiCodeReviewController(false, 100, $this->doctrine, $this->messageBus);
    }

    private function getControllerWithDebug(): AiCodeReviewController
    {
        $controller = new AiCodeReviewController(true, 100, $this->doctrine, $this->messageBus);
        $controller->setContainer($this->container);

        return $controller;
    }
}
