<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\User;

use DR\Review\Entity\User\UserAccessToken;
use DR\Review\Repository\User\UserAccessTokenRepository;
use DR\Review\Service\User\UserAccessTokenGenerator;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use RuntimeException;

/**
 * @coversDefaultClass \DR\Review\Service\User\UserAccessTokenGenerator
 * @covers ::__construct
 */
class UserAccessTokenGeneratorTest extends AbstractTestCase
{
    private UserAccessTokenRepository&MockObject $tokenRepository;
    private UserAccessTokenGenerator             $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tokenRepository = $this->createMock(UserAccessTokenRepository::class);
        $this->service         = new UserAccessTokenGenerator($this->tokenRepository);
    }

    /**
     * @covers ::generate
     * @throws Exception
     */
    public function testGenerateSuccess(): void
    {
        $this->tokenRepository->expects(self::once())
            ->method('findOneBy')
            ->willReturn(null);

        $identifier = $this->service->generate();
        static::assertSame(80, strlen($identifier));
    }

    /**
     * @covers ::generate
     * @throws Exception
     */
    public function testGenerateFailure(): void
    {
        $token = new UserAccessToken();

        $this->tokenRepository->expects(self::exactly(10))
            ->method('findOneBy')
            ->willReturn($token);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Failed to generate access token');
        $this->service->generate();
    }
}
