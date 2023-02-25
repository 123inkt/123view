<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api;

use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Utility\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

/**
 * @coversNothing
 */
class DocControllerTest extends AbstractFunctionalTestCase
{
    public function testHtmlDocs(): void
    {
        $this->client->request('GET', '/api/docs', server: ['HTTP_ACCEPT' => 'text/html']);
        self::assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        static::assertStringContainsString('swagger-ui', $content);
    }

    /**
     * @throws JsonException
     */
    public function testJsonDocs(): void
    {
        $this->client->request('GET', '/api/docs', server: ['HTTP_ACCEPT' => 'application/json']);
        self::assertResponseIsSuccessful();

        $data = Json::decode(Assert::notFalse($this->client->getResponse()->getContent()), true);
        static::assertArrayHasKey('openapi', $data);
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [];
    }
}
