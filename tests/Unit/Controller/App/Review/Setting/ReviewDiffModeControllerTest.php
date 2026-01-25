<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Setting;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Setting\ReviewDiffModeController;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Request\Review\Setting\ReviewDiffModeRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends AbstractControllerTestCase<ReviewDiffModeController>
 */
#[CoversClass(ReviewDiffModeController::class)]
class ReviewDiffModeControllerTest extends AbstractControllerTestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user            = new User;
        $reviewSetting   = new UserReviewSetting;
        $validatedRequest = $this->createMock(ReviewDiffModeRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects(static::once())->method('getDiffMode')->willReturn(ReviewDiffModeEnum::SIDE_BY_SIDE);
        $this->entityManager->expects(static::once())->method('flush');
        $this->expectRefererRedirect('/');
        $this->expectGetUser($user);

        $response = ($this->controller)($validatedRequest);
        static::assertSame(ReviewDiffModeEnum::SIDE_BY_SIDE, $reviewSetting->getReviewDiffMode());
    }

    public function getController(): AbstractController
    {
        return new ReviewDiffModeController($this->entityManager);
    }
}
