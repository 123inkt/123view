<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Security\AzureAd;

use DR\GitCommitNotification\Security\AzureAd\LoginFailure;
use DR\GitCommitNotification\Security\AzureAd\LoginService;
use DR\GitCommitNotification\Security\AzureAd\LoginSuccess;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use InvalidArgumentException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Contracts\Translation\TranslatorInterface;
use TheNetworg\OAuth2\Client\Provider\Azure;
use TheNetworg\OAuth2\Client\Token\AccessToken;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Security\AzureAd\LoginService
 * @covers ::__construct
 */
class LoginServiceTest extends AbstractTestCase
{
    /** @var MockObject&Azure */
    private Azure $azureProvider;
    /** @var MockObject&TranslatorInterface */
    private TranslatorInterface $translator;
    private LoginService        $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->azureProvider        = $this->createMock(Azure::class);
        $this->azureProvider->scope = ['scope'];
        $this->translator           = $this->createMock(TranslatorInterface::class);
        $this->service              = new LoginService($this->azureProvider, $this->translator);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginWasCancelled(): void
    {
        $request = new Request(
            [
                'error'             => 'error',
                'error_subcode'     => 'cancel',
                'error_description' => 'login was cancelled',
                'error_codes'       => [1, 2, 3],
                'error_uri'         => 'uri',
            ]
        );

        $this->translator->expects(self::once())->method('trans')->with('login.cancelled')->willReturn('login cancelled');

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginFailure::class, $result);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginErrorOccurred(): void
    {
        $request = new Request(
            [
                'error'             => 'error',
                'error_subcode'     => 'timeout',
                'error_description' => 'service timed out',
                'error_codes'       => [1, 2, 3],
                'error_uri'         => 'uri',
            ]
        );

        $this->translator->expects(self::once())->method('trans')->with('login.not.successful')->willReturn('login not successful');

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginFailure::class, $result);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginRequestShouldHaveCode(): void
    {
        $request = new Request([]);

        $this->translator->expects(self::once())->method('trans')->with('login.invalid.azuread.callback')->willReturn('invalid callback');

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginFailure::class, $result);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginAzureProviderThrowsException(): void
    {
        $request = new Request(['code' => '123abc']);

        $this->translator->expects(self::once())->method('trans')->with('login.unable.to.validate.login.attempt')->willReturn('invalid auth');
        $this->azureProvider
            ->expects(self::once())
            ->method('getAccessToken')
            ->with('authorization_code', ['scope' => ['scope'], 'code' => '123abc'])
            ->willThrowException(new InvalidArgumentException('failed'));

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginFailure::class, $result);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginAuthTokenHasNoUsername(): void
    {
        $request = new Request(['code' => '123abc']);

        $this->translator->expects(self::once())->method('trans')->with("login.authorization.has.no.token")->willReturn('invalid auth');
        $this->azureProvider
            ->expects(self::once())
            ->method('getAccessToken')
            ->with('authorization_code', ['scope' => ['scope'], 'code' => '123abc'])
            ->willReturn($this->createMock(AccessTokenInterface::class));

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginFailure::class, $result);
    }

    /**
     * @covers ::handleLogin
     */
    public function testHandleLoginSuccess(): void
    {
        $request = new Request(['code' => '123abc']);

        $this->translator->expects(self::never())->method('trans');

        $token = $this->createMock(AccessToken::class);
        $token->method('getIdTokenClaims')->willReturn(['name' => 'sherlock', 'preferred_username' => 'holmes@example.com']);
        $this->azureProvider
            ->expects(self::once())
            ->method('getAccessToken')
            ->with('authorization_code', ['scope' => ['scope'], 'code' => '123abc'])
            ->willReturn($token);

        $result = $this->service->handleLogin($request);
        static::assertInstanceOf(LoginSuccess::class, $result);
        static::assertSame('sherlock', $result->getName());
        static::assertSame('holmes@example.com', $result->getEmail());
    }
}
