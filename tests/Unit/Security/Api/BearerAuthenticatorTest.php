<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Security\Api;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Security\Api\BearerAuthenticator;
use DR\Review\Security\Role\Roles;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;

#[CoversClass(BearerAuthenticator::class)]
class BearerAuthenticatorTest extends AbstractTestCase
{
    private UserAccessTokenRepository&MockObject $tokenRepository;
    private BearerAuthenticator                  $authenticator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenRepository = $this->createMock(UserAccessTokenRepository::class);
        $this->authenticator   = new BearerAuthenticator($this->tokenRepository);
    }

    public function testSupports(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/api/test', 'HTTP_AUTHORIZATION' => 'Bearer 123view']);
        static::assertTrue($this->authenticator->supports($request));
    }

    public function testSupportsShouldSkipDocs(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/api/docs', 'HTTP_AUTHORIZATION' => 'Bearer 123view']);
        static::assertFalse($this->authenticator->supports($request));
    }

    public function testSupportsShouldSkipNonApiRequests(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/app/test']);
        static::assertFalse($this->authenticator->supports($request));
    }

    public function testSupportsShouldSkipAbsentAuthHeader(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/api/test']);
        static::assertFalse($this->authenticator->supports($request));
    }

    public function testSupportsShouldOnlyAcceptBearerAuthHeader(): void
    {
        $request = new Request(server: ['REQUEST_URI' => '/api/test', 'HTTP_AUTHORIZATION' => 'JWT 123view']);
        static::assertFalse($this->authenticator->supports($request));
    }

    public function testAuthenticateShouldThrowExceptionOnAbsentToken(): void
    {
        $request = new Request(server: ['HTTP_AUTHORIZATION' => 'Bearer 123view']);

        $this->tokenRepository->expects($this->once())->method('findOneBy')->with(['token' => '123view'])->willReturn(null);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Access denied');
        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateShouldThrowExceptionOnInsufficientPermission(): void
    {
        $request = new Request(server: ['HTTP_AUTHORIZATION' => 'Bearer 123view']);

        $user  = new User();
        $token = new UserAccessToken();
        $token->setUser($user);

        $this->tokenRepository->expects($this->once())->method('findOneBy')->with(['token' => '123view'])->willReturn($token);

        $this->expectException(AuthenticationException::class);
        $this->expectExceptionMessage('Access denied');
        $this->authenticator->authenticate($request);
    }

    public function testAuthenticateShouldSucceed(): void
    {
        $request = new Request(server: ['HTTP_AUTHORIZATION' => 'Bearer 123view']);

        $user = new User();
        $user->setEmail('email');
        $user->setRoles([Roles::ROLE_USER]);
        $token = new UserAccessToken();
        $token->setUsages(5);
        $token->setUser($user);

        $this->tokenRepository->expects($this->once())->method('findOneBy')->with(['token' => '123view'])->willReturn($token);
        $this->tokenRepository->expects($this->once())->method('save')->with($token, true);

        $passport = $this->authenticator->authenticate($request);
        static::assertInstanceOf(SelfValidatingPassport::class, $passport);
        static::assertSame($user, $passport->getUser());

        static::assertSame(6, $token->getUsages());
        static::assertNotNull($token->getUseTimestamp());
    }

    public function testOnAuthenticationSuccess(): void
    {
        static::assertNull($this->authenticator->onAuthenticationSuccess(new Request(), $this->createMock(TokenInterface::class), 'main'));
    }

    public function testOnAuthenticationFailure(): void
    {
        static::assertNull($this->authenticator->onAuthenticationFailure(new Request(), new AuthenticationException()));
    }
}
