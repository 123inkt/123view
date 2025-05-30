<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\EventSubscriber\ProblemJsonResponseSubscriber;
use DR\Review\Response\ProblemJsonResponse;
use DR\Review\Response\ProblemJsonResponseFactory;
use DR\Review\Tests\AbstractTestCase;
use Exception;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(ProblemJsonResponseSubscriber::class)]
class ProblemJsonResponseSubscriberTest extends AbstractTestCase
{
    private ProblemJsonResponseFactory&MockObject $responseFactory;
    private ProblemJsonResponseSubscriber         $subscriber;

    protected function setUp(): void
    {
        parent::setUp();
        $this->responseFactory = $this->createMock(ProblemJsonResponseFactory::class);
        $this->subscriber      = new ProblemJsonResponseSubscriber($this->responseFactory);
    }

    public function testOnKernelExceptionShouldSkipNonApiUrls(): void
    {
        $request = new Request();
        $event   = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Exception()
        );

        $this->responseFactory->expects(self::never())->method('createFromThrowable');

        $this->subscriber->onKernelException($event);
    }

    public function testOnKernelException(): void
    {
        $exception = new Exception();
        $request   = new Request(server: ['REQUEST_URI' => '/api/test']);
        $response  = new ProblemJsonResponse();
        $event     = new ExceptionEvent(
            $this->createMock(HttpKernelInterface::class),
            $request,
            HttpKernelInterface::MAIN_REQUEST,
            new Exception()
        );

        $this->responseFactory->expects($this->once())->method('createFromThrowable')->with($exception)->willReturn($response);

        $this->subscriber->onKernelException($event);
        static::assertSame($response, $event->getResponse());
    }

    public function testGetSubscribedEvents(): void
    {
        $expected = [KernelEvents::EXCEPTION => ['onKernelException', 2]];
        $result   = ProblemJsonResponseSubscriber::getSubscribedEvents();
        static::assertSame($expected, $result);
    }
}
