<?php

declare(strict_types=1);

namespace DR\Review\Tests\Functional\Mcp;

use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\CodeReviewFixtures;
use DR\Review\Tests\DataFixtures\UserAccessTokenFixtures;
use DR\Utils\Arrays;
use DR\Utils\Assert;
use Mcp\Server\Session\SessionFactory;
use Mcp\Server\Session\SessionStoreInterface;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

#[CoversNothing]
class GetCodeReviewToolTest extends AbstractFunctionalTestCase
{
    private const SERVER_HEADERS   = [
        'CONTENT_TYPE'       => 'application/json',
        'HTTP_ACCEPT'        => 'application/json, text/event-stream',
        'HTTP_AUTHORIZATION' => 'Bearer ' . UserAccessTokenFixtures::TOKEN_VALUE,
    ];

    /**
     * @throws Throwable
     */
    public function testGetCodeReviewByTitleFound(): void
    {
        $sessionId = $this->createMcpSession();

        $this->client->request(
            Request::METHOD_POST,
            '/_mcp',
            server : array_merge(self::SERVER_HEADERS, ['HTTP_MCP_SESSION_ID' => $sessionId]),
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 2,
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'get_code_review',
                    'arguments' => ['title' => 'title'],
                ],
            ]),
        );

        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);

        $text = Json::decode(Assert::string(Arrays::fetchByPath($data, ['result', 'content', 0, 'text'])), true);
        static::assertIsArray($text);
        static::assertSame(CodeReviewFixtures::PROJECT_ID, $text['id']);
        static::assertSame('title', $text['title']);
    }

    /**
     * @throws Throwable
     */
    public function testGetCodeReviewByTitleNotFound(): void
    {
        $sessionId = $this->createMcpSession();

        $this->client->request(
            Request::METHOD_POST,
            '/_mcp',
            server : array_merge(self::SERVER_HEADERS, ['HTTP_MCP_SESSION_ID' => $sessionId]),
            content: Json::encode([
                'jsonrpc' => '2.0',
                'id'      => 2,
                'method'  => 'tools/call',
                'params'  => [
                    'name'      => 'get-code-review',
                    'arguments' => ['title' => 'nonexistent-title'],
                ],
            ]),
        );

        static::assertResponseStatusCodeSame(Response::HTTP_OK);

        $data = $this->getResponseArray();
        static::assertSame('2.0', $data['jsonrpc']);
        static::assertArrayHasKey('result', $data);

        $text = Assert::string(Arrays::fetchByPath($data, ['result', 'content', 0, 'text']));
        static::assertSame('(null)', $text);
    }

    /**
     * @return list<class-string>
     */
    protected function getFixtures(): array
    {
        return [UserAccessTokenFixtures::class, CodeReviewFixtures::class];
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
