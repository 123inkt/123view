<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Mcp;

use DR\Review\Tests\AbstractFunctionalTestCase;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;

#[CoversNothing]
class McpEndpointTest extends AbstractFunctionalTestCase
{
    private const ENDPOINT = '/_mcp';

    public function testInitialize(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            self::ENDPOINT,
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => 'initialize',
                'params'  => [
                    'protocolVersion' => '2025-03-26',
                    'capabilities'    => (object) [],
                    'clientInfo'      => ['name' => 'functional-test', 'version' => '1.0.0'],
                ],
            ]),
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        static::assertNotEmpty($response->headers->get('Mcp-Session-Id'));

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertSame(1, $data['id']);
        static::assertArrayHasKey('result', $data);
        static::assertNotEmpty($data['result']['protocolVersion']);
    }

    public function testListTools(): void
    {
        $sessionId = $this->initializeMcpSession();

        $this->client->request(
            Request::METHOD_POST,
            self::ENDPOINT,
            content: Json::encode(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list']),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_MCP_SESSION_ID' => $sessionId],
        );

        self::assertResponseIsSuccessful();

        $data  = $this->getResponseArray();
        $tools = array_column($data['result']['tools'] ?? [], 'name');

        static::assertContains('get-code-review', $tools);
        static::assertContains('get-code-reviews', $tools);
    }

    /**
     * Perform the MCP handshake (initialize + notifications/initialized) and return the session ID.
     */
    private function initializeMcpSession(): string
    {
        $this->client->request(
            Request::METHOD_POST,
            self::ENDPOINT,
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => 'initialize',
                'params'  => [
                    'protocolVersion' => '2025-03-26',
                    'capabilities'    => (object) [],
                    'clientInfo'      => ['name' => 'functional-test', 'version' => '1.0.0'],
                ],
            ]),
            server: ['CONTENT_TYPE' => 'application/json'],
        );

        $sessionId = (string) $this->client->getResponse()->headers->get('Mcp-Session-Id');

        $this->client->request(
            Request::METHOD_POST,
            self::ENDPOINT,
            content: Json::encode(['jsonrpc' => '2.0', 'method' => 'notifications/initialized']),
            server: ['CONTENT_TYPE' => 'application/json', 'HTTP_MCP_SESSION_ID' => $sessionId],
        );

        return $sessionId;
    }

    protected function getFixtures(): array
    {
        return [];
    }
}
