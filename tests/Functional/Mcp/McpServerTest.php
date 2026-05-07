<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Mcp;

use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use Mcp\Server\Session\SessionFactory;
use Mcp\Server\Session\SessionStoreInterface;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[CoversNothing]
class McpServerTest extends AbstractFunctionalTestCase
{
    private const PROTOCOL_VERSION = '2024-11-05';
    private const SERVER_HEADERS   = [
        'CONTENT_TYPE'      => 'application/json',
        'HTTP_ACCEPT'       => 'application/json, text/event-stream',
        'HTTP_AUTHORIZATION' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE,
    ];

    /**
     * @throws Throwable
     */
    public function testUnauthenticated(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/_mcp',
            server : [
                'CONTENT_TYPE' => 'application/json',
                'HTTP_ACCEPT'  => 'application/json, text/event-stream',
            ],
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => 'initialize',
                'params'  => [
                    'protocolVersion' => self::PROTOCOL_VERSION,
                    'capabilities'    => (object)[],
                    'clientInfo'      => ['name' => 'test-client', 'version' => '1.0'],
                ],
            ]),
        );

        static::assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    /**
     * @throws Throwable
     */
    public function testInitialize(): void
    {
        $this->client->request(
            Request::METHOD_POST,
            '/_mcp',
            server : self::SERVER_HEADERS,
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 1,
                'method'  => 'initialize',
                'params'  => [
                    'protocolVersion' => self::PROTOCOL_VERSION,
                    'capabilities'    => (object)[],
                    'clientInfo'      => ['name' => 'test-client', 'version' => '1.0'],
                ],
            ]),
        );

        static::assertResponseIsSuccessful();

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertArrayHasKey('result', $data);
        static::assertIsArray($data['result']);
        static::assertIsString($data['result']['protocolVersion']);
        static::assertNotEmpty($this->client->getResponse()->headers->get('Mcp-Session-Id'));
    }

    /**
     * @throws Throwable
     */
    public function testToolsList(): void
    {
        $sessionId = $this->createMcpSession();

        $this->client->request(
            Request::METHOD_POST,
            '/_mcp',
            server : array_merge(self::SERVER_HEADERS, ['HTTP_MCP_SESSION_ID' => $sessionId]),
            content: Json::encode(['jsonrpc' => '2.0', 'id' => 2, 'method' => 'tools/list']),
        );

        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertArrayHasKey('result', $data);
        static::assertIsArray($data['result']);
        static::assertIsArray($data['result']['tools']);

        $toolNames = array_column($data['result']['tools'], 'name');
        static::assertCount(8, $toolNames);
        static::assertContains('get_code_review', $toolNames);
        static::assertContains('get_code_reviews', $toolNames);
        static::assertContains('get_code_review_diff', $toolNames);
        static::assertContains('get_code_review_comments', $toolNames);
        static::assertContains('get_current_user', $toolNames);
        static::assertContains('add_comment', $toolNames);
        static::assertContains('read_file', $toolNames);
        static::assertContains('list_files', $toolNames);
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [UserAccessTokenFixtures::class];
    }

    /**
     * Create an initialized MCP session directly via the session store service,
     * bypassing the HTTP handshake entirely.
     */
    private function createMcpSession(): string
    {
        $session = new SessionFactory()->create(static::getService(SessionStoreInterface::class, 'mcp.session.store'));
        $session->set('initialized', true);
        $session->set('client_info', ['name' => 'test-client', 'version' => '1.0']);
        $session->set('client_capabilities', []);
        $session->save();

        return $session->getId()->toRfc4122();
    }
}
