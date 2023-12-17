<?php
declare(strict_types=1);

namespace DR\Review\Controller;

use DR\Review\Entity\User\User;
use League\Uri\Http;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class AbstractController extends SymfonyAbstractController
{
    /**
     * @param array<string, (int|string|object|null)> $parameters
     * @param string[]                                $filter
     */
    public function refererRedirect(string $route, array $parameters = [], array $filter = [], ?string $anchor = null): RedirectResponse
    {
        /** @var RequestStack $stack */
        $stack = $this->container->get('request_stack');

        /** @var Request $request */
        $request = $stack->getCurrentRequest();

        $referer = $request->server->get('HTTP_REFERER');
        if (is_string($referer) === false) {
            $referer = null;
        }

        if ($referer !== null && count($filter) > 0) {
            $uri = Http::new($referer);
            parse_str($uri->getQuery(), $queryParams);
            foreach ($filter as $key) {
                unset($queryParams[$key]);
            }
            $referer = (string)$uri->withQuery(http_build_query($queryParams));
        }

        $url = $referer ?? $this->generateUrl($route, $parameters);
        $url .= $anchor !== null ? "#" . $anchor : '';

        return $this->redirect($url);
    }

    public function getUser(): User
    {
        $user = parent::getUser();
        if ($user === null || $user instanceof User === false) {
            throw new AccessDeniedException('Access denied');
        }

        return $user;
    }
}
