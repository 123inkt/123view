<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\User;
use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Service\User\UserAccessTokenGenerator;
use DR\Review\Service\User\UserAccessTokenIssuer;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(UserAccessTokenIssuer::class)]
class UserAccessTokenIssuerTest extends AbstractTestCase
{
    private UserAccessTokenGenerator&MockObject  $generator;
    private UserAccessTokenRepository&MockObject $accessTokenRepository;
    private UserAccessTokenIssuer                $issuer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generator             = $this->createMock(UserAccessTokenGenerator::class);
        $this->accessTokenRepository = $this->createMock(UserAccessTokenRepository::class);
        $this->issuer                = new UserAccessTokenIssuer($this->generator, $this->accessTokenRepository);
    }

    /**
     * @throws Exception
     */
    public function testIssue(): void
    {
        $user = new User();

        $this->generator->expects($this->once())->method('generate')->willReturn('token');
        $this->accessTokenRepository
            ->expects($this->once())
            ->method('save')
            ->with(
                static::callback(
                    static function (UserAccessToken $token) use ($user) {
                        static::assertSame('token', $token->getToken());
                        static::assertSame('name', $token->getName());
                        static::assertSame($user, $token->getUser());
                        static::assertGreaterThan(0, $token->getCreateTimestamp());
                        static::assertNull($token->getUseTimestamp());

                        return true;
                    }
                ),
                true
            );

        $this->issuer->issue($user, 'name');
    }
}
