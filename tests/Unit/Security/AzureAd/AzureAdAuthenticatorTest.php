<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use Doctrine\Persistence\ManagerRegistry;
use DR\GitCommitNotification\Controller\App\RulesController;
use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator;
use DR\GitCommitNotification\Security\AzureAd\AzureAdUserBadge;
use DR\GitCommitNotification\Security\AzureAd\LoginFailure;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use DR\GitCommitNotification\Security\AzureAd\LoginSuccess;
use DR\GitCommitNotification\Tests\AbstractTest;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\TestBrowserToken;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\AzureAdAuthenticator
 * @covers ::__construct
 */
class AzureAdAuthenticatorTest extends AbstractTest
{
    /** @var MockObject&LoginService */
    private LoginService $loginService;
    /** @var MockObject&ManagerRegistry */
    private ManagerRegistry $doctrine;
    /** @var MockObject&UrlGeneratorInterface */
    private UrlGeneratorInterface $urlGenerator;
    private AzureAdAuthenticator  $authenticator;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginService  = $this->createMock(LoginService::class);
        $this->doctrine      = $this->createMock(ManagerRegistry::class);
        $this->urlGenerator  = $this->createMock(UrlGeneratorInterface::class);
        $this->authenticator = new AzureAdAuthenticator($this->loginService, $this->doctrine, $this->urlGenerator);
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
        $request = new Request();
        $this->loginService->expects(self::once())->method('handleLogin')->with($request)->willReturn(new LoginSuccess('name', 'email'));

        $passport = $this->authenticator->authenticate($request);
        static::assertInstanceOf(SelfValidatingPassport::class, $passport);

        $badge  = $passport->getBadge(AzureAdUserBadge::class);
        $expect = new AzureAdUserBadge($this->doctrine, 'email', 'name');
        static::assertEquals($expect, $badge);
    }

    /**
     * @covers ::onAuthenticationSuccess
     */
    public function testOnAuthenticationSuccess(): void
    {
        $url = '/my/test/url';
        $this->urlGenerator->expects(self::once())->method('generate')->with(RulesController::class)->willReturn($url);

        $result = $this->authenticator->onAuthenticationSuccess(new Request(), new TestBrowserToken(), 'main');
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
            ->with(AuthenticationController::class, ['error_message' => 'error'])
            ->willReturn($url);

        $result = $this->authenticator->onAuthenticationFailure(new Request(), new AuthenticationException('error'));
        $expect = new RedirectResponse($url);

        static::assertEquals($expect, $result);
    }
}
