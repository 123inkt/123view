<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\Gitlab\UserGitlabDisconnectController;
use DR\Review\Controller\App\User\UserGitSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\GitAccessTokenRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractControllerTestCase<UserGitlabDisconnectController>
 */
#[CoversClass(UserGitlabDisconnectController::class)]
class UserGitlabDisconnectControllerTest extends AbstractControllerTestCase
{
    private GitAccessTokenRepository&MockObject $tokenRepository;

    protected function setUp(): void
    {
        $this->tokenRepository = $this->createMock(GitAccessTokenRepository::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $token = new GitAccessToken();
        $token->setGitType(RepositoryGitType::GITLAB);
        $user = new User();
        $user->getGitAccessTokens()->add($token);

        $this->expectGetUser($user);
        $this->expectAddFlash('success', 'gitlab.comment.sync.disabled');
        $this->expectRedirectToRoute(UserGitSyncController::class)->willReturn('url');
        $this->tokenRepository->expects(self::once())->method('remove')->with($token, true);

        ($this->controller)();
    }

    public function testInvokeShouldSkipSave(): void
    {
        $user = new User();

        $this->expectGetUser($user);
        $this->expectAddFlash('success', 'gitlab.comment.sync.disabled');
        $this->expectRedirectToRoute(UserGitSyncController::class)->willReturn('url');
        $this->tokenRepository->expects(self::never())->method('remove');

        ($this->controller)();
    }

    public function getController(): AbstractController
    {
        return new UserGitlabDisconnectController($this->tokenRepository);
    }
}
