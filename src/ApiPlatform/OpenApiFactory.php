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

        // Add additional explanation to the OpenApi authorization dialog description
        $schemes = Assert::notNull($openApi->getComponents()->getSecuritySchemes());
        /** @var SecurityScheme|null $scheme */
        $scheme = $schemes['Bearer'] ?? null;
        if ($scheme !== null) {
            $description       = '**Header**: Authorization';
            $description       .= '<br>**Format**: Bearer &lt;token&gt;.';
            $description       .= '<br>**Token**: [Create here](' . $this->urlGenerator->generate(UserAccessTokenController::class) . ').';
            $description       .= '<br><br>';
            $schemes['Bearer'] = $scheme->withDescription($description);
        }

        return $openApi;
    }
}
