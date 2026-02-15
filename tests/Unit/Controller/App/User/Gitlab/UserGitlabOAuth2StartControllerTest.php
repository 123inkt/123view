<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\User\Gitlab;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\User\Gitlab\UserGitlabOAuth2StartController;
use DR\Review\Tests\AbstractControllerTestCase;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use function DR\PHPUnitExtensions\Mock\consecutive;

/**
 * @extends AbstractControllerTestCase<UserGitlabOAuth2StartController>
 */
#[CoversClass(UserGitlabOAuth2StartController::class)]
class UserGitlabOAuth2StartControllerTest extends AbstractControllerTestCase
{
    private OAuth2Provider&MockObject $authProvider;

    protected function setUp(): void
    {
        $this->authProvider = $this->createMock(OAuth2Provider::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $session = static::createStub(SessionInterface::class);
        $session->method('set')->with(...consecutive(['gitlab.oauth2.state', 'state'], ['gitlab.oauth2.pkce', 'pkce']));

        $request = new Request();
        $request->setSession($session);

        $this->authProvider->expects($this->once())->method('getAuthorizationUrl')->willReturn('url');
        $this->authProvider->expects($this->once())->method('getState')->willReturn('state');
        $this->authProvider->expects($this->once())->method('getPkceCode')->willReturn('pkce');

        $response = ($this->controller)($request);
        static::assertEquals(new RedirectResponse('url'), $response);
    }

    public function getController(): AbstractController
    {
        return new UserGitlabOAuth2StartController($this->authProvider);
    }
}
