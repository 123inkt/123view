<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Webhook;

use DR\Review\RemoteEvent\GitlabRemoteEvent;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Webhook\GitlabRequestParser;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Webhook\Exception\RejectWebhookException;

#[CoversClass(GitlabRequestParser::class)]
class GitlabRequestParserTest extends AbstractTestCase
{
    private GitlabRequestParser $parser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->parser = new GitlabRequestParser();
    }

    public function testParserSuccess(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD'           => 'POST',
                'HTTP_X_GITLAB_EVENT'      => 'Push Hook',
                'HTTP_X_GITLAB_TOKEN'      => '1234567890',
                'HTTP_X_GITLAB_EVENT_UUID' => 'uuid',
            ],
            content: '{"object_kind":"push"}'
        );

        $event = $this->parser->parse($request, '1234567890');
        static::assertInstanceOf(GitlabRemoteEvent::class, $event);
        static::assertSame('Push Hook', $event->getName());
        static::assertSame('uuid', $event->getId());
        static::assertSame(['object_kind' => 'push'], $event->getPayload());
    }

    public function testParserUnknownEvent(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD'      => 'POST',
                'HTTP_X_GITLAB_EVENT' => 'Foobar',
                'HTTP_X_GITLAB_TOKEN' => '1234567890',
            ],
            content: '{"object_kind":"push"}'
        );

        static::assertNull($this->parser->parse($request, '1234567890'));
    }

    public function testParserInvalidSecret(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD'      => 'POST',
                'HTTP_X_GITLAB_EVENT' => 'Push Hook',
                'HTTP_X_GITLAB_TOKEN' => '1234567890',
            ],
            content: '{"object_kind":"push"}'
        );

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Access denied');
        static::assertNull($this->parser->parse($request, 'foobar'));
    }

    public function testParserInvalidMethod(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD'      => 'GET',
                'HTTP_X_GITLAB_EVENT' => 'Push Hook',
                'HTTP_X_GITLAB_TOKEN' => '1234567890',
            ],
            content: '{"object_kind":"push"}'
        );

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Request does not match.');
        static::assertNull($this->parser->parse($request, '1234567890'));
    }

    public function testParserMissingHeaders(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD' => 'POST',
            ],
            content: '{"object_kind":"push"}'
        );

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Request does not match.');
        static::assertNull($this->parser->parse($request, '1234567890'));
    }

    public function testParserInvalidBody(): void
    {
        $request = new Request(
            server : [
                'REQUEST_METHOD'      => 'POST',
                'HTTP_X_GITLAB_EVENT' => 'Push Hook',
                'HTTP_X_GITLAB_TOKEN' => '1234567890',
            ],
            content: '{foobar'
        );

        $this->expectException(RejectWebhookException::class);
        $this->expectExceptionMessage('Request does not match.');
        static::assertNull($this->parser->parse($request, '1234567890'));
    }
}
