<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Controller\Api;

use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Utils\Assert;
use Nette\Utils\Json;
use Nette\Utils\JsonException;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class DocControllerTest extends AbstractFunctionalTestCase
{
    public function testHtmlDocs(): void
    {
        $this->client->request('GET', '/api/docs', server: ['HTTP_ACCEPT' => 'text/html']);
        self::assertResponseIsSuccessful();

        $content = $this->client->getResponse()->getContent();
        static::assertIsString($content);
        static::assertStringContainsString('swagger-ui', $content);
    }

    /**
     * @throws JsonException
     */
    public function testJsonDocs(): void
    {
        $this->client->request('GET', '/api/docs', server: ['HTTP_ACCEPT' => 'application/vnd.openapi+json']);
        self::assertResponseIsSuccessful();

        $data = Json::decode(Assert::notFalse($this->client->getResponse()->getContent()), true);
        static::assertIsArray($data);
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
