<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Config\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController as SymfonyAbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @codeCoverageIgnore
 */
abstract class AbstractController extends SymfonyAbstractController
{
    /**
     * @param array<string, (int|string|null)> $parameters
     */
    public function refererRedirect(string $route, array $parameters = []): RedirectResponse
    {
        /** @var RequestStack $stack */
        $stack = $this->container->get('request_stack');

        /** @var Request $request */
        $request = $stack->getCurrentRequest();

        $referer = $request->server->get('HTTP_REFERER');
        if (is_string($referer) === false) {
            $referer = null;
        }

        return $this->redirect($referer ?? $this->generateUrl($route, $parameters));
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
