<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Setting;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Setting\DiffComparisonPolicyController;
use DR\Review\Entity\Git\Diff\DiffComparePolicy;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Request\Review\Setting\DiffComparisonPolicyRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @extends AbstractControllerTestCase<DiffComparisonPolicyController>
 */
#[CoversClass(DiffComparisonPolicyController::class)]
class DiffComparisonPolicyControllerTest extends AbstractControllerTestCase
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
        $validatedRequest = $this->createMock(DiffComparisonPolicyRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects(static::once())->method('getComparisonPolicy')->willReturn(DiffComparePolicy::TRIM);
        $this->entityManager->expects(static::once())->method('flush');
        $this->expectRefererRedirect('/');
        $this->expectGetUser($user);

        ($this->controller)($validatedRequest);
        static::assertSame(DiffComparePolicy::TRIM, $reviewSetting->getDiffComparisonPolicy());
    }

    public function getController(): AbstractController
    {
        return new DiffComparisonPolicyController($this->entityManager);
    }
}
