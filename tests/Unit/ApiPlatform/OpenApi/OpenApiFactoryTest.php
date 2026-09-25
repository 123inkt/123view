<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Components;
use ApiPlatform\OpenApi\Model\Info;
use ApiPlatform\OpenApi\Model\Operation;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\PathItem;
use ApiPlatform\OpenApi\Model\Paths;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use ArrayObject;
use DR\Review\ApiPlatform\OpenApi\OpenApiFactory;
use DR\Review\ApiPlatform\OpenApi\OperationParameterDocumentor;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(OpenApiFactory::class)]
class OpenApiFactoryTest extends AbstractTestCase
{
    private OpenApiFactoryInterface&MockObject      $openApiFactory;
    private UrlGeneratorInterface&MockObject        $urlGenerator;
    private OperationParameterDocumentor&MockObject $parameterDocumentor;
    private OpenApiFactory                          $factory;

    protected function setUp(): void
    {
        parent::setUp();
        $this->openApiFactory      = $this->createMock(OpenApiFactoryInterface::class);
        $this->urlGenerator        = $this->createMock(UrlGeneratorInterface::class);
        $this->parameterDocumentor = $this->createMock(OperationParameterDocumentor::class);
        $this->factory             = new OpenApiFactory($this->openApiFactory, $this->urlGenerator, $this->parameterDocumentor);
    }

    /**
     * @throws Exception
     */
    public function testInvoke(): void
    {
        $context         = ['context' => true];
        $securityScheme  = new SecurityScheme();
        $securitySchemes = new ArrayObject(['Bearer' => $securityScheme]);
        $components      = new Components(securitySchemes: $securitySchemes);

        $parameter = new Parameter('foo', 'bar');
        $operation = new Operation(parameters: [$parameter]);
        $paths     = new Paths();
        $paths->addPath('foo', new PathItem(get: $operation));

        $openApi = new OpenApi(new Info('title', '1.0.0'), [], $paths, $components);

        $this->openApiFactory->expects($this->once())->method('__invoke')->with($context)->willReturn($openApi);
        $this->urlGenerator->expects($this->once())->method('generate')->willReturn('url');
        $this->parameterDocumentor->expects($this->once())->method('getDescription')->with($operation, $parameter)->willReturn('description');

        // run test
        ($this->factory)($context);

        // assert
        static::assertInstanceOf(SecurityScheme::class, $securitySchemes['Bearer']);
        static::assertStringContainsString('**Header**: Authorization: Bearer', $securitySchemes['Bearer']->getDescription());
        static::assertSame('bearer', $securitySchemes['Bearer']->getScheme());
        static::assertSame('http', $securitySchemes['Bearer']->getType());
        static::assertSame('description', $parameter->getDescription());
    }
}
