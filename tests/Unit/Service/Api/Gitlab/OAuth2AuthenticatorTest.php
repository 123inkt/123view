<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\User\GitAccessToken;
use DR\Review\Entity\User\User;
use DR\Review\Repository\User\GitAccessTokenRepository;
use DR\Review\Service\Api\Gitlab\OAuth2Authenticator;
use DR\Review\Tests\AbstractTestCase;
use League\OAuth2\Client\Grant\RefreshToken;
use League\OAuth2\Client\Provider\GenericProvider as OAuth2Provider;
use League\OAuth2\Client\Token\AccessToken;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(OAuth2Authenticator::class)]
class OAuth2AuthenticatorTest extends AbstractTestCase
{
    private OAuth2Provider&MockObject           $authProvider;
    private GitAccessTokenRepository&MockObject $tokenRepository;
    private OAuth2Authenticator                 $authenticator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->authProvider    = $this->createMock(OAuth2Provider::class);
        $this->tokenRepository = $this->createMock(GitAccessTokenRepository::class);
        $this->authenticator   = new OAuth2Authenticator($this->authProvider, $this->tokenRepository);
    }

    /**
     * @throws Throwable
     */
    public function testGetAuthorizationHeaderNotExpired(): void
    {
        $gitToken = new GitAccessToken();
        $gitToken->setToken(Json::encode(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires_in' => 1234]));

        $this->tokenRepository->expects($this->never())->method('save');

        static::assertSame('Bearer access', $this->authenticator->getAuthorizationHeader($gitToken));
    }

    /**
     * @throws Throwable
     */
    public function testGetAuthorizationHeaderExpiredToken(): void
    {
        $json = Json::encode(['access_token' => 'access', 'refresh_token' => 'refresh', 'expires' => time() - 1000]);

        $gitToken = new GitAccessToken();
        $gitToken->setToken($json);
        $gitToken->setUser(new User());

        $accessToken = new AccessToken(['access_token' => 'refreshed-token', 'refresh_token' => 'refresh', 'expires' => time() + 1000]);

        $this->authProvider->expects($this->once())
            ->method('getAccessToken')
            ->with(new RefreshToken(), ['refresh_token' => 'refresh'])
            ->willReturn($accessToken);
        $this->tokenRepository->expects($this->once())->method('save')->with($gitToken, true);

        static::assertSame('Bearer refreshed-token', $this->authenticator->getAuthorizationHeader($gitToken));
    }
}
