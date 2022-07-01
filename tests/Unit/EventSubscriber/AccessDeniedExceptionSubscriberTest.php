<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\EventSubscriber;

use DR\GitCommitNotification\Controller\Auth\AuthenticationController;
use DR\GitCommitNotification\EventSubscriber\AccessDeniedExceptionSubscriber;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\EventSubscriber\AccessDeniedExceptionSubscriber
 * @covers ::__construct
 */
class AccessDeniedExceptionSubscriberTest extends AbstractTestCase
{
    /** @var MockObject&TranslatorInterface */
    private TranslatorInterface $translator;
    /** @var MockObject&UrlGeneratorInterface */
    private UrlGeneratorInterface $urlGenerator;

    private AccessDeniedExceptionSubscriber $subscriber;

    protected function setUp(): void
    {
        parent::setUp();

        $this->translator   = $this->createMock(TranslatorInterface::class);
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->subscriber   = new AccessDeniedExceptionSubscriber($this->urlGenerator, $this->translator);
    }

    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        $expected = [KernelEvents::EXCEPTION => ['onKernelException', 2]];
        $result   = AccessDeniedExceptionSubscriber::getSubscribedEvents();
        static::assertSame($expected, $result);
    }

    /**
     * @covers ::onKernelException
     */
    public function testOnKernelExceptionOnlyAcceptAccessDeniedException(): void
    {
        $this->translator->expects(self::never())->method('trans');
        $this->urlGenerator->expects(self::never())->method('generate');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new Exception('exception')
        );
        $this->subscriber->onKernelException($event);
    }

    /**
     * @covers ::onKernelException
     */
    public function testOnKernelExceptionShouldRedirect(): void
    {
        $this->translator->expects(self::once())->method('trans')->with('redirect.access.denied.session.expired')->willReturn('message');
        $this->urlGenerator
            ->expects(self::once())
            ->method('generate')
            ->with(AuthenticationController::class, ['error_message' => 'message'])
            ->willReturn('url');

        $event = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            new Request(),
            HttpKernelInterface::MAIN_REQUEST,
            new AccessDeniedException('access-denied')
        );
        $this->subscriber->onKernelException($event);

        static::assertEquals(new RedirectResponse('url'), $event->getResponse());
    }
}
