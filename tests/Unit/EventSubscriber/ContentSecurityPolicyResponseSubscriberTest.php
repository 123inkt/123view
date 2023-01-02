<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\EventSubscriber\ContentSecurityPolicyResponseSubscriber;
use DR\Review\Tests\AbstractTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * @coversDefaultClass \DR\Review\EventSubscriber\ContentSecurityPolicyResponseSubscriber
 * @covers ::__construct
 */
class ContentSecurityPolicyResponseSubscriberTest extends AbstractTestCase
{
    /**
     * @covers ::getSubscribedEvents
     */
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([KernelEvents::RESPONSE => 'onResponse'], ContentSecurityPolicyResponseSubscriber::getSubscribedEvents());
    }

    /**
     * @covers ::onResponse
     */
    public function testOnResponse(): void
    {
        $response   = new Response();
        $event      = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), 1, $response);
        $subscriber = new ContentSecurityPolicyResponseSubscriber('host');
        $subscriber->onResponse($event);

        static::assertSame(
            "default-src 'self'; img-src 'self' data:; object-src: 'none'; connect-src 'self' host:*",
            $response->headers->get("Content-Security-Policy")
        );
    }
}
