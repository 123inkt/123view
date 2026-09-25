<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Service\User\UserAccessTokenGenerator;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

#[CoversClass(UserAccessTokenGenerator::class)]
class UserAccessTokenGeneratorTest extends AbstractTestCase
{
    private UserAccessTokenRepository&MockObject $tokenRepository;
    private UserAccessTokenGenerator             $generator;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenRepository = $this->createMock(UserAccessTokenRepository::class);
        $this->generator       = new UserAccessTokenGenerator($this->tokenRepository);
    }

    /**
     * @throws Exception
     */
    public function testGenerateSuccess(): void
    {
        $this->tokenRepository->expects($this->once())
            ->method('findOneBy')
            ->willReturn(null);

        $identifier = $this->generator->generate();
        static::assertSame(80, strlen($identifier));
    }

    /**
     * @throws Exception
     */
    public function testGenerateFailure(): void
    {
        $token = new UserAccessToken();

        $this->tokenRepository->expects($this->exactly(10))
            ->method('findOneBy')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate access token');
        $this->generator->generate();
    }
}
