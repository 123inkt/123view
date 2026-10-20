<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\EventSubscriber;

use DR\Review\EventSubscriber\McpStreamResponseSubscriber;
use DR\Review\Tests\AbstractTestCase;
use Generator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;

#[CoversClass(McpStreamResponseSubscriber::class)]
class McpStreamResponseSubscriberTest extends AbstractTestCase
{
    public function testGetSubscribedEvents(): void
    {
        static::assertSame([KernelEvents::RESPONSE => 'onResponse'], McpStreamResponseSubscriber::getSubscribedEvents());
    }

    #[DataProvider('provideEventStreamContentTypes')]
    public function testClosesBuffersBeforeStreaming(string $contentType): void
    {
        $state = (object)['bufferLevel' => null];
        $response = new StreamedResponse(static function () use ($state): void {
            $state->bufferLevel = ob_get_level();
        }, headers: ['Content-Type' => $contentType]);
        $event = new ResponseEvent(
            static::createStub(HttpKernelInterface::class),
            Request::create('/_mcp', Request::METHOD_POST),
            HttpKernelInterface::MAIN_REQUEST,
            $response
        );

        (new McpStreamResponseSubscriber())->onResponse($event);

        $bufferLevel = ob_get_level();
        // A non-flushable buffer protects PHPUnit's output capture.
        ob_start(null, 0, PHP_OUTPUT_HANDLER_REMOVABLE);
        ob_start();
        ob_start();
        try {
            $response->sendContent();
        } finally {
            while (ob_get_level() > $bufferLevel) {
                ob_end_clean();
            }
        }

        static::assertSame($bufferLevel + 1, $state->bufferLevel);
    }

    /**
     * @return Generator<string, array{string}>
     */
    public static function provideEventStreamContentTypes(): Generator
    {
        yield 'SSE' => ['text/event-stream'];
        yield 'SSE with charset' => ['text/event-stream; charset=UTF-8'];
    }

    #[DataProvider('provideIgnoredResponses')]
    public function testIgnoresOtherResponses(string $path, int $requestType, Response $response): void
    {
        $callback = $response instanceof StreamedResponse ? $response->getCallback() : null;
        $event = new ResponseEvent(
            static::createStub(HttpKernelInterface::class),
            Request::create($path),
            $requestType,
            $response
        );

        (new McpStreamResponseSubscriber())->onResponse($event);

        static::assertSame($response, $event->getResponse());
        if ($response instanceof StreamedResponse) {
            static::assertSame($callback, $response->getCallback());
        }
    }

    /**
     * @return Generator<string, array{string, int, Response}>
     */
    public static function provideIgnoredResponses(): Generator
    {
        $sse = new StreamedResponse(static function (): void {
        }, headers: ['Content-Type' => 'text/event-stream']);

        yield 'other endpoint' => ['/other', HttpKernelInterface::MAIN_REQUEST, clone $sse];
        yield 'subrequest' => ['/_mcp', HttpKernelInterface::SUB_REQUEST, clone $sse];
        yield 'non-streamed response' => ['/_mcp', HttpKernelInterface::MAIN_REQUEST, new Response()];
        yield 'JSON response' => [
            '/_mcp',
            HttpKernelInterface::MAIN_REQUEST,
            new StreamedResponse(static function (): void {
            }, headers: ['Content-Type' => 'application/json']),
        ];
        yield 'missing callback' => [
            '/_mcp',
            HttpKernelInterface::MAIN_REQUEST,
            new StreamedResponse(headers: ['Content-Type' => 'text/event-stream']),
        ];
    }
}
