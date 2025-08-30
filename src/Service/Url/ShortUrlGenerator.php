<?php
declare(strict_types=1);

namespace DR\Review\Service\Url;

use DR\Review\Controller\App\Url\ShortUrlController;
use League\Uri\Http;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;

readonly class ShortUrlGenerator
{
    public function __construct(private UrlGeneratorInterface $urlGenerator, private ShortUrlCreationService $shortUrlCreationService)
    {
    }

    /**
     * Generate a short URL for the given route and parameters
     *
     * @param string               $name          The name of the route
     * @param array<string, mixed> $parameters    An array of parameters
     * @param int                  $referenceType The type of reference (one of the constants in UrlGeneratorInterface)
     *
     * @throws Throwable
     */
    public function generate(string $name, array $parameters = [], int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        // Generate the original URL using the Symfony URL generator
        $originalUrl = $this->urlGenerator->generate($name, $parameters, $referenceType);

        // Create shortened URL entity
        $shortUrl = $this->shortUrlCreationService->createShortUrl(Http::new($originalUrl));

        // Generate and return the short URL pointing to the ShortUrlController
        return $this->urlGenerator->generate(ShortUrlController::class, ['shortKey' => $shortUrl->getShortKey()], $referenceType);
    }
}
