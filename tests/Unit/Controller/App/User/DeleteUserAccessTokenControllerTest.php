<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\DeleteUserAccessTokenController;
use DR\Review\Controller\App\User\UserSettingController;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Voter\UserAccessTokenVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\Controller\App\User\DeleteUserAccessTokenController
 * @covers ::__construct
 */
class DeleteUserAccessTokenControllerTest extends AbstractControllerTestCase
{
    private UserAccessTokenRepository&MockObject $tokenRepository;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->createMock(UserAccessTokenRepository::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $token = new UserAccessToken();

        $this->expectDenyAccessUnlessGranted(UserAccessTokenVoter::DELETE, $token);
        $this->tokenRepository->expects(self::once())->method('remove')->with($token, true);
        $this->expectAddFlash('success', 'access.token.deletion.success');
        $this->expectRedirectToRoute(UserSettingController::class);
    }

    public function getController(): AbstractController
    {
        return new DeleteUserAccessTokenController($this->tokenRepository);
    }
}
