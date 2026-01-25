<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Setting;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Setting\ReviewDiffModeController;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Repository\User\UserReviewSettingRepository;
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
    private UserReviewSettingRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserReviewSettingRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user            = new User;
        $reviewSetting   = new UserReviewSetting;
        $validatedRequest = $this->createMock(ReviewDiffModeRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects(static::once())->method('getDiffMode')->willReturn(ReviewDiffModeEnum::SIDE_BY_SIDE);
        $this->repository->expects(static::once())->method('save')->with($reviewSetting, true);
        $this->expectRefererRedirect('/');
        $this->expectGetUser($user);

        ($this->controller)($validatedRequest);
        static::assertSame(ReviewDiffModeEnum::SIDE_BY_SIDE, $reviewSetting->getReviewDiffMode());
    }

    public function getController(): AbstractController
    {
        return new ReviewDiffModeController($this->repository);
    }
}
