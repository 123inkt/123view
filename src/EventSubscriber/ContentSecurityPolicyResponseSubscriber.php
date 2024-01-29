<?php
declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class ContentSecurityPolicyResponseSubscriber implements EventSubscriberInterface
{
    public function __construct(private readonly string $hostname, private readonly bool $ideUrlEnabled, private readonly string $ideUrlPattern)
    {
    }

    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if ($response->headers->has("Content-Security-Policy")) {
            return;
        }

        // only allow content from own host.
        // allow image svg+xml
        // allow websocket to connect to any port.
        $policy = [
            "default-src 'self' https://cdn.jsdelivr.net",
            "img-src 'self' data:",
            "object-src 'none'",
            "base-uri 'none'",
            sprintf("connect-src 'self' %s:*", $this->hostname),
        ];

        // if IDE url is allowed, allow iframe host from http or https url
        if ($this->ideUrlEnabled && preg_match('#^(https?://[^:/]+)#', $this->ideUrlPattern, $matches) === 1) {
            $policy[] = sprintf("frame-src %s:*", $matches[1]);
        }

        $response->headers->set("Content-Security-Policy", implode('; ', $policy));
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }
}
