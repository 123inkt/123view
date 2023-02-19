<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use DR\Review\ApiPlatform\OpenApiFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @coversDefaultClass \DR\Review\ApiPlatform\OpenApiFactory
 * @covers ::__construct
 */
class OpenApiFactoryTest extends AbstractTestCase
{
    private OpenApiFactoryInterface&MockObject $openApiFactory;
    private UrlGeneratorInterface&MockObject   $urlGenerator;
    private OpenApiFactory                     $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openApiFactory = $this->createMock(OpenApiFactoryInterface::class);
        $this->urlGenerator   = $this->createMock(UrlGeneratorInterface::class);
        $this->factory        = new OpenApiFactory($this->openApiFactory, $this->urlGenerator);
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $context         = ['context' => true];
        $securityScheme  = new SecurityScheme();
        $securitySchemes = new ArrayObject(['Bearer' => $securityScheme]);
        $components      = new Components(securitySchemes: $securitySchemes);
        $openApi         = new OpenApi(new Info('title', '1.0.0'), [], new Paths(), $components);

        $this->openApiFactory->expects(self::once())->method('__invoke')->with($context)->willReturn($openApi);
        $this->urlGenerator->expects(self::once())->method('generate')->willReturn('url');

        // run test
        ($this->factory)($context);

        // assert
        static::assertInstanceOf(SecurityScheme::class, $securitySchemes['Bearer']);
        static::assertStringContainsString('Bearer &lt;token&gt;', $securitySchemes['Bearer']->getDescription());
    }
}
