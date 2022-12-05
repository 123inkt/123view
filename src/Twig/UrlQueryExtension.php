<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class UrlQueryExtension extends AbstractExtension
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [new TwigFunction('url_query_params', [$this, 'getUrlQuery'], ['is_safe' => ['all']])];
    }

    /**
     * @param array<string, int|string> $queryParams
     */
    public function getUrlQuery(array $queryParams): string
    {
        $request = $this->requestStack->getCurrentRequest();
        if ($request === null) {
            return '?' . http_build_query($queryParams);
        }

        $params = $request->query->all();

        foreach ($queryParams as $key => $value) {
            $params[$key] = $value;
        }

        return '?' . http_build_query($params);
    }
}
