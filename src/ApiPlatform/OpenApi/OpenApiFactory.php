<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform\OpenApi;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\Parameter;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\Controller\App\User\UserAccessTokenController;
use DR\Review\Utility\Assert;
use Exception;
use ReflectionClass;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(
        private readonly OpenApiFactoryInterface $decorated,
        private readonly UrlGeneratorInterface $urlGenerator,
        private readonly OperationParameterDocumentor $documentor
    ) {
    }

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        // Change scheme to bearer+http and add explanation to the OpenApi authorization dialog description.
        // @see https://swagger.io/docs/specification/authentication/bearer-authentication/
        $schemes = Assert::notNull($openApi->getComponents()->getSecuritySchemes());
        /** @var SecurityScheme|null $scheme */
        $scheme = $schemes['Bearer'] ?? null;
        if ($scheme !== null) {
            $url               = $this->urlGenerator->generate(UserAccessTokenController::class);
            $description       = '**Header**: Authorization: Bearer &lt;[Token](' . $url . ')&gt;.';
            $schemes['Bearer'] = $scheme->withDescription($description)->withScheme("bearer")->withType('http');
        }

        // the description is missing for certain parameters, and the ApiPlatform classes don't allow to hook into the generated descriptions.
        // Unfortunately we have to resort to reflection to set our own description here.
        foreach (new OpenApiParameterIterator($openApi) as [$operation, $parameter]) {
            $this->setParameterDescription($parameter, $this->documentor->getDescription($operation, $parameter));
        }

        $finder = (new Finder())->files()->in(__DIR__ . '/../../../resources/openapi')->name('*.php');
        foreach ($finder as $openApiFile) {
            (require $openApiFile)($openApi);
        }

        return $openApi;
    }

    /**
     * @throws Exception
     */
    private function setParameterDescription(Parameter $parameter, mixed $value): void
    {
        $reflectionProperty = (new ReflectionClass($parameter))->getProperty('description');
        /** @noinspection PhpExpressionResultUnusedInspection */
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($parameter, $value);
    }
}
