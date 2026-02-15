<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Setting;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Setting\DiffComparisonPolicyController;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Repository\User\UserReviewSettingRepository;
use DR\Review\Request\Review\Setting\DiffComparisonPolicyRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<DiffComparisonPolicyController>
 */
#[CoversClass(DiffComparisonPolicyController::class)]
class DiffComparisonPolicyControllerTest extends AbstractControllerTestCase
{
    private UserReviewSettingRepository&MockObject $repository;

    protected function setUp(): void
    {
        $this->repository = $this->createMock(UserReviewSettingRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user            = new User();
        $reviewSetting   = new UserReviewSetting();
        $validatedRequest = $this->createMock(DiffComparisonPolicyRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects($this->once())->method('getComparisonPolicy')->willReturn(DiffComparePolicy::TRIM);
        $this->repository->expects($this->once())->method('save')->with($reviewSetting, true);
        $this->expectRefererRedirect('/');
        $this->expectGetUser($user);

        ($this->controller)($validatedRequest);
        static::assertSame(DiffComparePolicy::TRIM, $reviewSetting->getDiffComparisonPolicy());
    }

    public function getController(): AbstractController
    {
        return new DiffComparisonPolicyController($this->repository);
    }
}
