<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use DR\Review\Service\Url\ShortUrlGenerator;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Throwable;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class ShortUrlExtension extends AbstractExtension
{
    public function __construct(private readonly ShortUrlGenerator $shortUrlGenerator)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('short_url', $this->generateShortUrl(...)),];
    }

    /**
     * Generate a short URL for the given route and parameters
     *
     * @param array<string, mixed> $parameters An array of parameters
     *
     * @throws Throwable
     */
    public function generateShortUrl(string $name, array $parameters = []): string
    {
        return $this->shortUrlGenerator->generate($name, $parameters, UrlGeneratorInterface::ABSOLUTE_URL);
    }
}
