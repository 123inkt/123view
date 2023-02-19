<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\Utility\Assert;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private readonly OpenApiFactoryInterface $decorated)
    {
    }

    /**
     * @inheritDoc
     */
    public function __invoke(array $context = []): OpenApi
    {
        $openApi = ($this->decorated)($context);

        // Add additional explanation to the OpenApi authorization dialog description
        $schemes = Assert::notNull($openApi->getComponents()->getSecuritySchemes());
        /** @var SecurityScheme|null $scheme */
        $scheme = $schemes['Bearer'] ?? null;
        if ($scheme !== null) {
            $schemes['Bearer'] = $scheme->withDescription($scheme->getDescription() . ' Format: `Bearer <token>`.');
        }

        return $openApi;
    }
}
