<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Setting;

use Doctrine\ORM\EntityManagerInterface;
use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Setting\DiffVisibleLinesController;
use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserReviewSetting;
use DR\Review\Request\Review\Setting\DiffVisibleLinesRequest;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<DiffVisibleLinesController>
 */
#[CoversClass(DiffVisibleLinesController::class)]
class DiffVisibleLinesControllerTest extends AbstractControllerTestCase
{
    private EntityManagerInterface&MockObject $entityManager;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $user             = new User();
        $reviewSetting    = new UserReviewSetting();
        $validatedRequest = $this->createMock(DiffVisibleLinesRequest::class);

        $user->setReviewSetting($reviewSetting);

        $validatedRequest->expects(static::once())->method('getVisibleLines')->willReturn(10);
        $this->entityManager->expects(static::once())->method('flush');
        $this->expectRefererRedirect('/');
        $this->expectGetUser($user);

        ($this->controller)($validatedRequest);
        static::assertSame(10, $reviewSetting->getDiffVisibleLines());
    }

    public function getController(): AbstractController
    {
        return new DiffVisibleLinesController($this->entityManager);
    }
}
