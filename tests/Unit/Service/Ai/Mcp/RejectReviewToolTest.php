<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Doctrine\Type\CodeReviewerStateType;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Mcp\RejectReviewTool;
use DR\Review\Service\CodeReview\ChangeReviewerStateService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[CoversClass(RejectReviewTool::class)]
class RejectReviewToolTest extends AbstractTestCase
{
    private Security&MockObject                  $security;
    private CodeReviewRepository&MockObject      $reviewRepository;
    private ChangeReviewerStateService&MockObject $changeReviewerStateService;
    private RejectReviewTool                     $tool;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security                   = $this->createMock(Security::class);
        $this->reviewRepository           = $this->createMock(CodeReviewRepository::class);
        $this->changeReviewerStateService = $this->createMock(ChangeReviewerStateService::class);
        $this->tool                       = new RejectReviewTool($this->security, $this->reviewRepository, $this->changeReviewerStateService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeThrowsWhenReviewNotFound(): void
    {
        $this->security->expects($this->never())->method('getUser');
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->changeReviewerStateService->expects($this->never())->method('changeState');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldRejectReviewSuccessfully(): void
    {
        $user   = new User();
        $review = new CodeReview();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->reviewRepository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->changeReviewerStateService->expects($this->once())->method('changeState')
            ->with($review, $user, CodeReviewerStateType::REJECTED);

        $result = ($this->tool)(456);
        static::assertSame('Review rejected. Reviewers state: ' . $review->getReviewersState(), $result);
    }
}
