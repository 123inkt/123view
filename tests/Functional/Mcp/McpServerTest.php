<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Mcp;

use DR\Review\Tests\AbstractFunctionalTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class McpServerTest extends AbstractFunctionalTestCase
{
    private const MCP_ENDPOINT      = '/_mcp';
    private const PROTOCOL_VERSION  = '2024-11-05';
    private const SERVER_HEADERS    = [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_ACCEPT'  => 'application/json, text/event-stream',
    ];

    public function testInitialize(): void
    {
        $this->client->request(
            method:  Request::METHOD_POST,
            uri:     self::MCP_ENDPOINT,
            server:  self::SERVER_HEADERS,
            content: $this->buildInitializePayload(),
        );

        static::assertResponseIsSuccessful();

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertArrayHasKey('result', $data);
        static::assertIsString($data['result']['protocolVersion']);
        static::assertNotEmpty($this->client->getResponse()->headers->get('Mcp-Session-Id'));
    }

    public function testToolsList(): void
    {
        $sessionId = $this->initializeMcpSession();

        $this->client->request(
            method:  Request::METHOD_POST,
            uri:     self::MCP_ENDPOINT,
            server:  array_merge(self::SERVER_HEADERS, ['HTTP_MCP_SESSION_ID' => $sessionId]),
            content: json_encode(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list'], \JSON_THROW_ON_ERROR),
        );

        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertArrayHasKey('result', $data);

        $toolNames = array_column($data['result']['tools'], 'name');
        static::assertContains('get-code-review', $toolNames);
        static::assertContains('get-code-reviews', $toolNames);
    }

    protected function getFixtures(): array
    {
        return [];
    }

    private function initializeMcpSession(): string
    {
        $this->client->request(
            method:  Request::METHOD_POST,
            uri:     self::MCP_ENDPOINT,
            server:  self::SERVER_HEADERS,
            content: $this->buildInitializePayload(),
        );

        $sessionId = $this->client->getResponse()->headers->get('Mcp-Session-Id');
        static::assertNotNull($sessionId, 'MCP session ID missing from initialize response.');

        return $sessionId;
    }

    private function buildInitializePayload(): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'id'      => 1,
            'method'  => 'initialize',
            'params'  => [
                'protocolVersion' => self::PROTOCOL_VERSION,
                'capabilities'    => (object) [],
                'clientInfo'      => ['name' => 'test-client', 'version' => '1.0'],
            ],
        ], \JSON_THROW_ON_ERROR);
    }
}
