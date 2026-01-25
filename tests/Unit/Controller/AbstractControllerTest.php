<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller;

use DR\Review\Controller\AbstractController;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @extends AbstractControllerTestCase<AbstractController>
 */
#[CoversClass(AbstractController::class)]
class AbstractControllerTest extends AbstractControllerTestCase
{
    public function testGetUser(): void
    {
        $user = new User();
        $this->expectGetUser($user);

        static::assertSame($user, $this->controller->getUser());
    }

    public function testGetUserShouldThrowExceptionOnAbsentUser(): void
    {
        $this->expectGetUser(null);

        $this->expectException(AccessDeniedException::class);
        $this->expectExceptionMessage('Access denied');
        $this->controller->getUser();
    }

    public function testRefererRedirect(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => 'referer']);
        $requestStack = new RequestStack([$request]);
        $this->container->set('request_stack', $requestStack);

        $response = $this->controller->refererRedirect('route');
        static::assertEquals(new RedirectResponse('referer'), $response);
    }

    public function testRefererRedirectInvalidRefererShouldBeSkipped(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => false]);
        $requestStack = new RequestStack([$request]);
        $this->container->set('request_stack', $requestStack);

        $this->expectGenerateUrl('route', [])->willReturn('url');

        $response = $this->controller->refererRedirect('route', []);
        static::assertEquals(new RedirectResponse('url'), $response);
    }

    public function testRefererRedirectShouldFilterQueryParam(): void
    {
        $request      = new Request(server: ['HTTP_REFERER' => 'https://referer?foo=bar&action=great']);
        $requestStack = new RequestStack([$request]);
        $this->container->set('request_stack', $requestStack);

        $response = $this->controller->refererRedirect('route', [], ['action']);
        static::assertEquals(new RedirectResponse('https://referer?foo=bar'), $response);
    }

    public function getController(): AbstractController
    {
        return new class () extends AbstractController {
            public function __invoke(): void
            {
                // nothing
            }
        };
    }
}
