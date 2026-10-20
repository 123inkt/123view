<?php

declare(strict_types=1);

namespace DR\Review\EventSubscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class McpStreamResponseSubscriber implements EventSubscriberInterface
{
    public function onResponse(ResponseEvent $event): void
    {
        $response = $event->getResponse();
        if (!$event->isMainRequest()
            || $event->getRequest()->getPathInfo() !== '/_mcp'
            || !$response instanceof StreamedResponse
            || trim(explode(';', $response->headers->get('Content-Type', ''))[0]) !== 'text/event-stream'
        ) {
            return;
        }

        $callback = $response->getCallback();
        if ($callback === null) {
            return;
        }

        $response->setCallback(static function () use ($callback): void {
            // The MCP SDK calls flush(), which does not flush PHP's output buffers.
            Response::closeOutputBuffers(0, true);
            flush();
            $callback();
        });
    }

    /**
     * @inheritdoc
     */
    public static function getSubscribedEvents(): array
    {
        return [KernelEvents::RESPONSE => 'onResponse'];
    }
}
