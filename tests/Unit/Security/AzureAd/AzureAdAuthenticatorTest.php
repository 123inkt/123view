<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use DR\GitCommitNotification\Controller\App\Review\ProjectsController;
use DR\GitCommitNotification\Controller\App\User\UserApprovalPendingController;
use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadgeFactory;
use DR\GitCommitNotification\Security\AzureAd\LoginFailure;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use DR\GitCommitNotification\Security\AzureAd\LoginSuccess;
use DR\GitCommitNotification\Security\Role\Roles;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use JsonException;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator
 * @covers ::__construct
 */
class AzureAdAuthenticatorTest extends AbstractTestCase
{
    private LoginService&MockObject            $loginService;
    private UrlGeneratorInterface&MockObject   $urlGenerator;
    private AzureAdUserBadgeFactory&MockObject $badgeFactory;
    private AzureAdAuthenticator               $authenticator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginService  = $this->createMock(LoginService::class);
        $this->badgeFactory  = $this->createMock(AzureAdUserBadgeFactory::class);
        $this->urlGenerator  = $this->createMock(UrlGeneratorInterface::class);
        $this->authenticator = new AzureAdAuthenticator($this->loginService, $this->badgeFactory, $this->urlGenerator);
    }

    /**
     * @covers ::supports
     */
    public function testSupportsFailure(): void
    {
        $request = new Request(server: ['REQUEST_URI' => 'foobar']);
        static::assertFalse($this->authenticator->supports($request));
    }

    /**
     * @covers ::supports
     */
    public function testSupportsAccepts(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/single-sign-on/azure-ad/callback']);
        static::assertTrue($this->authenticator->supports($request));
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateFailure(): void
    {
        $request = new Request();
        $this->loginService->expects(self::once())->method('handleLogin')->with($request)->willReturn(new LoginFailure('failed'));

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('failed');
        $this->authenticator->authenticate($request);
    }

    /**
     * @covers ::authenticate
     */
    public function testAuthenticateSuccess(): void
    {
        $badge = new UserBadge('email');

        $request = new Request();
        $this->loginService->expects(self::once())->method('handleLogin')->with($request)->willReturn(new LoginSuccess('name', 'email'));
        $this->badgeFactory->expects(self::once())->method('create')->with('email', 'name')->willReturn($badge);

        $passport = $this->authenticator->authenticate($request);
        static::assertInstanceOf(SelfValidatingPassport::class, $passport);

        static::assertSame($badge, $passport->getBadge(UserBadge::class));
    }

    /**
     * @covers ::onAuthenticationSuccess
     * @throws JsonException
     */
    public function testOnAuthenticationSuccessForNewUser(): void
    {
        $url = '/my/test/url';
        $this->urlGenerator->expects(self::once())->method('generate')->with(UserApprovalPendingController::class)->willReturn($url);

        $result = $this->authenticator->onAuthenticationSuccess(new Request(), new TestBrowserToken(), 'main');
        $expect = new RedirectResponse($url);

        static::assertEquals($expect, $result);
    }

    /**
     * @covers ::onAuthenticationSuccess
     * @throws JsonException
     */
    public function testOnAuthenticationSuccess(): void
    {
        $url = '/my/test/url';
        $this->urlGenerator->expects(self::once())->method('generate')->with(ProjectsController::class)->willReturn($url);

        $result = $this->authenticator->onAuthenticationSuccess(new Request(), new TestBrowserToken([Roles::ROLE_USER]), 'main');
        $expect = new RedirectResponse($url);

        static::assertEquals($expect, $result);
    }

    /**
     * @covers ::onAuthenticationSuccess
     * @throws JsonException
     */
    public function testOnAuthenticationSuccessWithNextUrl(): void
    {
        $request = new Request(['state' => '{"next":"https://foo/bar/"}']);
        $url     = 'https://foo/bar/';
        $this->urlGenerator->expects(self::never())->method('generate');

        $result = $this->authenticator->onAuthenticationSuccess($request, new TestBrowserToken([Roles::ROLE_USER]), 'main');
        $expect = new RedirectResponse($url);

        static::assertEquals($expect, $result);
    }

    /**
     * @covers ::onAuthenticationFailure
     */
    public function testOnAuthenticationFailure(): void
    {
        $url = '/my/test/url';
        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with(AuthenticationController::class)
            ->willReturn($url);

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $result = $this->authenticator->onAuthenticationFailure($request, new AuthenticationException('error'));
        $expect = new RedirectResponse($url);

        static::assertEquals($expect, $result);
    }
}
