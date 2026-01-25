<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\Review\CommentVisibilityEnum;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Service\CodeReview\UserReviewSettingsProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Review\ReviewDiffModeEnum;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;

#[CoversClass(UserReviewSettingsProvider::class)]
class UserReviewSettingsProviderTest extends AbstractTestCase
{
    private Security&MockObject           $security;
    private UserReviewSettingsProvider    $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->security = $this->createMock(Security::class);
        $this->provider = new UserReviewSettingsProvider($this->security);
    }

    public function testGetVisibleLinesWithoutUser(): void
    {
        $this->security->expects($this->once())->method('getUser')->willReturn(null);

        static::assertSame(6, $this->provider->getVisibleLines());
    }

    public function testGetVisibleLinesWithUser(): void
    {
        $user    = new User();
        $setting = (new UserReviewSetting())->setUser($user)->setDiffVisibleLines(10);
        $user->setReviewSetting($setting);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);

        static::assertSame(10, $this->provider->getVisibleLines());
    }

    public function testGetComparisonPolicyWithoutUser(): void
    {
        $this->security->expects($this->once())->method('getUser')->willReturn(null);

        static::assertSame(DiffComparePolicy::ALL, $this->provider->getComparisonPolicy());
    }

    public function testGetComparisonPolicyWithUser(): void
    {
        $user    = new User();
        $setting = (new UserReviewSetting())->setUser($user)->setDiffComparisonPolicy(DiffComparePolicy::TRIM);
        $user->setReviewSetting($setting);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);

        static::assertSame(DiffComparePolicy::TRIM, $this->provider->getComparisonPolicy());
    }

    public function testGetReviewDiffModeWithoutUser(): void
    {
        $this->security->expects($this->once())->method('getUser')->willReturn(null);

        static::assertSame(ReviewDiffModeEnum::INLINE, $this->provider->getReviewDiffMode());
    }

    public function testGetReviewDiffModeWithUser(): void
    {
        $user    = new User();
        $setting = (new UserReviewSetting())->setUser($user)->setReviewDiffMode(ReviewDiffModeEnum::UNIFIED);
        $user->setReviewSetting($setting);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);

        static::assertSame(ReviewDiffModeEnum::UNIFIED, $this->provider->getReviewDiffMode());
    }

    public function testGetCommentVisibilityWithoutUser(): void
    {
        $this->security->expects($this->once())->method('getUser')->willReturn(null);

        static::assertSame(CommentVisibilityEnum::ALL, $this->provider->getCommentVisibility());
    }

    public function testGetCommentVisibilityWithUser(): void
    {
        $user    = new User();
        $setting = (new UserReviewSetting())->setUser($user)->setReviewCommentVisibility(CommentVisibilityEnum::UNRESOLVED);
        $user->setReviewSetting($setting);
        $this->security->expects($this->once())->method('getUser')->willReturn($user);

        static::assertSame(CommentVisibilityEnum::UNRESOLVED, $this->provider->getCommentVisibility());
    }
}
