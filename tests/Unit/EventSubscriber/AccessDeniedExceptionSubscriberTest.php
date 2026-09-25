<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\Controller\Auth\LoginController;
use DR\Review\EventSubscriber\AccessDeniedExceptionSubscriber;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockArraySessionStorage;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[CoversClass(AccessDeniedExceptionSubscriber::class)]
class AccessDeniedExceptionSubscriberTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private AccessDeniedExceptionSubscriber  $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->subscriber   = new AccessDeniedExceptionSubscriber($this->urlGenerator);
    }

    public function testGetSubscribedEvents(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');
        $expected = [KernelEvents::EXCEPTION => ['onKernelException', 2]];
        $result   = AccessDeniedExceptionSubscriber::getSubscribedEvents();
        static::assertSame($expected, $result);
    }

    public function testOnKernelExceptionOnlyAcceptAccessDeniedException(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');

        $event = new ExceptionEvent(
            static::createStub(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Exception('exception')
        );
        $this->subscriber->onKernelException($event);
    }

    public function testOnKernelExceptionShouldSkipApiCalls(): void
    {
        $this->urlGenerator->expects($this->never())->method('generate');

        $event = new ExceptionEvent(
            static::createStub(HttpKernelInterface::class),
            new Request(server: ['REQUEST_URI' => '/api/test']),
            HttpKernelInterface::MAIN_REQUEST,
            new AccessDeniedException('access-denied')
        );
        $this->subscriber->onKernelException($event);
    }

    public function testOnKernelExceptionShouldRedirect(): void
    {
        $this->urlGenerator
            ->expects($this->once())
            ->method('generate')
            ->with(LoginController::class)
            ->willReturn('url');

        $request = new Request();
        $request->setSession(new Session(new MockArraySessionStorage()));

        $event = new ExceptionEvent(
            static::createStub(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new AccessDeniedException('access-denied')
        );
        $this->subscriber->onKernelException($event);

        static::assertEquals(new RedirectResponse('url'), $event->getResponse());
    }
}
