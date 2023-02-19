<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Provider;

use ApiPlatform\Api\UrlGeneratorInterface;
use ApiPlatform\Metadata\Get;
use ApiPlatform\State\ProviderInterface;
use DR\Review\ApiPlatform\Provider\CodeReviewProvider;
use DR\Review\Service\User\UserService;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\Provider\CodeReviewProvider
 * @covers ::__construct
 */
class CodeReviewProviderTest extends AbstractTestCase
{
    private ProviderInterface&MockObject     $collectionProvider;
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private UserService&MockObject           $userService;
    private CodeReviewProvider               $reviewProvider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->collectionProvider = $this->createMock(ProviderInterface::class);
        $this->urlGenerator       = $this->createMock(UrlGeneratorInterface::class);
        $this->userService        = $this->createMock(UserService::class);
        $this->reviewProvider     = new CodeReviewProvider($this->collectionProvider, $this->urlGenerator, $this->userService);
    }

    /**
     * @covers ::provide
     */
    public function testProvideShouldOnlySupportGetCollection(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Only GetCollection operation is supported');
        $this->reviewProvider->provide(new Get());
    }

    /**
     * @covers ::provide
     */
    public function testProvide(): void
    {
    }
}
