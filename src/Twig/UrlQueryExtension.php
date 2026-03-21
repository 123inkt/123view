<?php
declare(strict_types=1);

namespace DR\Review\Twig;

use Symfony\Component\HttpFoundation\RequestStack;
use Twig\Attribute\AsTwigFunction;

class UrlQueryExtension
{
    public function __construct(private readonly RequestStack $requestStack)
    {
    }

    /**
     * @param array<string, int|string> $queryParams
     */
    #[AsTwigFunction(name: 'url_query_params', isSafe: ['all'])]
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
