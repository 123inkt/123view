<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @coversDefaultClass \DR\Review\Controller\AbstractController
 */
class AbstractControllerTest extends AbstractControllerTestCase
{
    /**
     * @covers ::getUser
     */
    public function testGetUser(): void
    {
        $user = new User();
        $this->expectGetUser($user);

        static::assertSame($user, $this->controller->getUser());
    }

    /**
     * @covers ::getUser
     */
    public function testGetUserShouldThrowExceptionOnAbsentUser(): void
    {
        $this->expectGetUser(null);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        $this->controller->getUser();
    }

    /**
     * @covers ::refererRedirect
     */
    public function testRefererRedirect(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => 'referer']);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $response = $this->controller->refererRedirect('route');
        static::assertEquals(new RedirectResponse('referer'), $response);
    }

    /**
     * @covers ::refererRedirect
     */
    public function testRefererRedirectInvalidRefererShouldBeSkipped(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => false]);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $this->expectGenerateUrl('route', [])->willReturn('url');

        $response = $this->controller->refererRedirect('route', []);
        static::assertEquals(new RedirectResponse('url'), $response);
    }

    /**
     * @covers ::refererRedirect
     */
    public function testRefererRedirectShouldFilterQueryParam(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => 'http://referer?foo=bar&action=great']);
        $requestStack = new RequestStack();
        $requestStack->push($request);
        $this->container->set('request_stack', $requestStack);

        $response = $this->controller->refererRedirect('route', [], ['action']);
        static::assertEquals(new RedirectResponse('http://referer?foo=bar'), $response);
    }

    public function getController(): AbstractController
    {
        /** @var AbstractController&callable $mock */
        $mock = $this->getMockForAbstractClass(
            originalClassName      : AbstractController::class,
            callOriginalConstructor: false
        );

        return $mock;
    }
}
