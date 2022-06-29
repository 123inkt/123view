<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

abstract class AbstractControllerTestCase extends AbstractTestCase
{
    /** @var AbstractController&callable */
    protected AbstractController $controller;
    protected Container          $container;

    protected function setUp(): void
    {
        parent::setUp();
        $this->controller = $this->getController();
        $this->container  = new Container();
        $this->controller->setContainer($this->container);
    }

    public function expectAddFlash(string $type, mixed $message): void
    {
        $flashBag = $this->createMock(FlashBagInterface::class);
        $request  = new Request();
        $request->setSession(new Session(new MockArraySessionStorage(), null, $flashBag));
        $requestStack = new RequestStack();
        $requestStack->push($request);

        $this->container->set('request_stack', $requestStack);

        $flashBag->expects(self::once())->method('add')->with($type, $message);
    }

    /**
     * @param array<string, string> $parameters
     * @return InvocationMocker<RouterInterface>
     */
    public function expectGenerateUrl(
        string $route,
        array $parameters = [],
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): InvocationMocker {
        $router = $this->createMock(RouterInterface::class);
        $this->container->set('router', $router);

        return $router->expects(self::once())->method('generate')->with($route, $parameters, $referenceType);
    }

    /**
     * @return AbstractController&callable
     */
    abstract public function getController(): AbstractController;
}
