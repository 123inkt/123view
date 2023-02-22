<?php
declare(strict_types=1);

namespace DR\Review\ApiPlatform;

use ApiPlatform\OpenApi\Factory\OpenApiFactoryInterface;
use ApiPlatform\OpenApi\Model\SecurityScheme;
use ApiPlatform\OpenApi\OpenApi;
use DR\Review\Controller\App\User\UserAccessTokenController;
use DR\Review\Utility\Assert;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class OpenApiFactory implements OpenApiFactoryInterface
{
    public function __construct(private readonly OpenApiFactoryInterface $decorated, private readonly UrlGeneratorInterface $urlGenerator)
    {
    }

    /**
     * @inheritDoc
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

        return $openApi;
    }
}
