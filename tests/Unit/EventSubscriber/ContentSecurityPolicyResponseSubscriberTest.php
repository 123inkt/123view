<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\EventSubscriber\ContentSecurityPolicyResponseSubscriber;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(ContentSecurityPolicyResponseSubscriber::class)]
class ContentSecurityPolicyResponseSubscriberTest extends AbstractTestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([KernelEvents::RESPONSE => 'onResponse'], ContentSecurityPolicyResponseSubscriber::getSubscribedEvents());
    }

    public function testOnResponseShouldNotOverrideExisting(): void
    {
        $response = new Response();
        $response->headers->set('Content-Security-Policy', '');
        $event      = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), 1, $response);
        $subscriber = new ContentSecurityPolicyResponseSubscriber('host', true, 'url');

        $subscriber->onResponse($event);

        static::assertSame("", $response->headers->get("Content-Security-Policy"));
    }

    public function testOnResponseWithIdeUrl(): void
    {
        $response   = new Response();
        $event      = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), 1, $response);
        $subscriber = new ContentSecurityPolicyResponseSubscriber('host', true, 'http://localhost:8080/file');
        $subscriber->onResponse($event);

        static::assertSame(
            "default-src 'self' https://cdn.jsdelivr.net/npm/bootstrap; img-src 'self' data:; object-src 'none'; base-uri 'none'; " .
            "connect-src 'self' host:*; frame-src http://localhost:*",
            $response->headers->get("Content-Security-Policy")
        );
    }

    public function testOnResponseWithoutIdeUrl(): void
    {
        $response   = new Response();
        $event      = new ResponseEvent($this->createMock(HttpKernelInterface::class), new Request(), 1, $response);
        $subscriber = new ContentSecurityPolicyResponseSubscriber('host', false, 'http://localhost:8080/file');
        $subscriber->onResponse($event);

        static::assertSame(
            "default-src 'self' https://cdn.jsdelivr.net/npm/bootstrap; img-src 'self' data:; object-src 'none';" .
            " base-uri 'none'; connect-src 'self' host:*",
            $response->headers->get("Content-Security-Policy")
        );
    }
}
