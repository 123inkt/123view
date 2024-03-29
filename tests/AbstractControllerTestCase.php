<?php
declare(strict_types=1);

namespace DR\Review\Tests;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\User\User;
use DR\Review\Tests\Helper\FormAssertion;
use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Twig\Environment;

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

    public function expectGetUser(?User $user): void
    {
        $token = $this->createMock(TokenInterface::class);
        $token->expects(self::atLeastOnce())->method('getUser')->willReturn($user);

        $storage = $this->createMock(TokenStorageInterface::class);
        $storage->expects(self::atLeastOnce())->method('getToken')->willReturn($token);

        $this->container->set('security.token_storage', $storage);
    }

    public function expectDenyAccessUnlessGranted(string $attribute, mixed $subject, bool $granted = true): void
    {
        $checker = $this->createMock(AuthorizationCheckerInterface::class);
        $checker->expects(self::atLeastOnce())->method('isGranted')->with($attribute, $subject)->willReturn($granted);

        $this->container->set('security.authorization_checker', $checker);
    }

    /**
     * @param array<string, int|string|object|object[]|null> $options
     */
    public function expectCreateForm(string $type, mixed $data = null, array $options = []): FormAssertion
    {
        $form = $this->createMock(FormInterface::class);

        $factory = $this->createMock(FormFactoryInterface::class);
        $factory->expects(self::once())->method('create')->with($type, $data, $options)->willReturn($form);

        $this->container->set('form.factory', $factory);

        return new FormAssertion($form, $this);
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
     * @param array<string, int|string|object|null> $parameters
     *
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
     * @param array<string, int|string|object|null> $parameters
     *
     * @return InvocationMocker<RouterInterface>
     */
    public function expectRedirectToRoute(string $route, array $parameters = []): InvocationMocker
    {
        return $this->expectGenerateUrl($route, $parameters);
    }

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

    /**
     * @param array<string, int|string|object> $parameters
     */
    public function expectRender(string $view, array $parameters = [], string $renderOutput = 'output'): void
    {
        $twig = $this->createMock(Environment::class);
        $this->container->set('twig', $twig);

        $twig->expects(self::once())->method('render')->with($view, $parameters)->willReturn($renderOutput);
    }

    /**
     * @return AbstractController&callable
     */
    abstract public function getController(): AbstractController;
}
