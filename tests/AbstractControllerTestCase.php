<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use DR\PHPUnitExtensions\Symfony\AbstractControllerTestCase as ExtensionsAbstractControllerTestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @template T as AbstractController&callable
 * @extends ExtensionsAbstractControllerTestCase<T>
 */
abstract class AbstractControllerTestCase extends ExtensionsAbstractControllerTestCase
{
    /**
     * @param array<string, int|string|object> $parameters
     */
    public function expectRefererRedirect(string $route, array $parameters = [], string $redirectTo = 'redirect'): void
    {
        if ($this->container->has('request_stack') === false) {
            $requestStack = new RequestStack();
            $this->container->set('request_stack', $requestStack);
        } else {
            /** @var RequestStack $requestStack */
            $requestStack = $this->container->get('request_stack');
        }

        if ($requestStack->getCurrentRequest() === null) {
            $request = new Request();
            $requestStack->push($request);
        }

        $this->expectGenerateUrl($route, $parameters)->willReturn($redirectTo);
    }
}
