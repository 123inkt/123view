<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2FinishController;
use DR\Review\Controller\App\User\UserGitSyncController;
use DR\Review\Doctrine\Type\RepositoryGitType;
use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\GitAccessTokenRepository;
use DR\Review\Tests\AbstractControllerTestCase;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use function DR\PHPUnitExtensions\Mock\consecutive;

/**
 * @extends AbstractControllerTestCase<UserGitlabOAuth2FinishController>
 */
#[CoversClass(UserGitlabOAuth2FinishController::class)]
class UserGitlabOAuth2FinishControllerTest extends AbstractControllerTestCase
{
    private SessionInterface&MockObject         $session;
    private OAuth2Provider&MockObject           $authProvider;
    private GitAccessTokenRepository&MockObject $tokenRepository;

    protected function setUp(): void
    {
        $this->session         = $this->createMock(SessionInterface::class);
        $this->authProvider    = $this->createMock(OAuth2Provider::class);
        $this->tokenRepository = $this->createMock(GitAccessTokenRepository::class);
        parent::setUp();
    }

    public function testInvokeAbsentCode(): void
    {
        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Missing `code` query parameter');
        ($this->controller)(new Request());
    }

    public function testInvokeInvalidState(): void
    {
        $this->session->expects($this->exactly(2))->method('get')->willReturn('foobar', 'pkce');
        $this->session->expects($this->exactly(2))->method('remove');

        $request = new Request(['code' => 'code', 'state' => 'invalid']);
        $request->setSession($this->session);

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Invalid state');
        ($this->controller)($request);
    }

    public function testInvokeUpdateGitAccessToken(): void
    {
        $this->session->expects($this->exactly(2))->method('get')
            ->with(...consecutive(['gitlab.oauth2.state'], ['gitlab.oauth2.pkce']))
            ->willReturn('state', 'pkce');
        $this->session->expects($this->exactly(2))->method('remove')->with(...consecutive(['gitlab.oauth2.state'], ['gitlab.oauth2.pkce']));

        $request = new Request(['code' => 'code', 'state' => 'state']);
        $request->setSession($this->session);

        $gitAccessToken = new GitAccessToken();
        $gitAccessToken->setGitType(RepositoryGitType::GITLAB);
        $user = new User();
        $user->getGitAccessTokens()->add($gitAccessToken);
        $this->expectGetUser($user);

        $accessToken = new AccessToken(['access_token' => 'token']);

        $this->expectAddFlash('success', 'gitlab.comment.sync.enabled');
        $this->expectRedirectToRoute(UserGitSyncController::class)->willReturn('url');
        $this->authProvider->expects($this->once())->method('getAccessToken')
            ->with('authorization_code', ['code' => 'code'])
            ->willReturn($accessToken);
        $this->tokenRepository->expects($this->once())->method('save')->with($gitAccessToken, true);

        ($this->controller)($request);
    }

    public function testInvokeCreateGitAccessToken(): void
    {
        $this->session->expects($this->exactly(2))->method('get')
            ->with(...consecutive(['gitlab.oauth2.state'], ['gitlab.oauth2.pkce']))
            ->willReturn('state', 'pkce');
        $this->session->expects($this->exactly(2))->method('remove')->with(...consecutive(['gitlab.oauth2.state'], ['gitlab.oauth2.pkce']));

        $request = new Request(['code' => 'code', 'state' => 'state']);
        $request->setSession($this->session);

        $user = new User();
        $this->expectGetUser($user);

        $accessToken = new AccessToken(['access_token' => 'token']);

        $this->expectAddFlash('success', 'gitlab.comment.sync.enabled');
        $this->expectRedirectToRoute(UserGitSyncController::class)->willReturn('url');
        $this->authProvider->expects($this->once())->method('getAccessToken')
            ->with('authorization_code', ['code' => 'code'])
            ->willReturn($accessToken);
        $this->tokenRepository->expects($this->once())->method('save');

        ($this->controller)($request);
    }

    public function getController(): AbstractController
    {
        return new UserGitlabOAuth2FinishController($this->authProvider, $this->tokenRepository);
    }
}
