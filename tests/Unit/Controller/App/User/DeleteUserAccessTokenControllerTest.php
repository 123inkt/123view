<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\DeleteUserAccessTokenController;
use DR\Review\Controller\App\User\UserAccessTokenController;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Voter\UserAccessTokenVoter;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<DeleteUserAccessTokenController>
 */
#[CoversClass(DeleteUserAccessTokenController::class)]
class DeleteUserAccessTokenControllerTest extends AbstractControllerTestCase
{
    private UserAccessTokenRepository&MockObject $tokenRepository;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->createMock(UserAccessTokenRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $token = new UserAccessToken();

        $this->expectDenyAccessUnlessGranted(UserAccessTokenVoter::DELETE, $token);
        $this->tokenRepository->expects(self::once())->method('remove')->with($token, true);
        $this->expectAddFlash('success', 'access.token.deletion.success');
        $this->expectRedirectToRoute(UserAccessTokenController::class)->willReturn('url');

        ($this->controller)($token);
    }

    public function getController(): AbstractController
    {
        return new DeleteUserAccessTokenController($this->tokenRepository);
    }
}
