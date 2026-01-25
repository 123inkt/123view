<?php
declare(strict_types=1);

namespace DR\Review\Tests\Functional\Webhook;

use DR\Review\Entity\Repository\RepositoryProperty;
use DR\Review\Repository\Config\RepositoryPropertyRepository;
use DR\Review\Repository\Config\RepositoryRepository;
use DR\Review\Service\RemoteEvent\RemoteEventHandler;
use DR\Review\Tests\AbstractFunctionalTestCase;
use DR\Review\Tests\DataFixtures\RepositoryFixtures;
use DR\Utils\Assert;
use Exception;
use Nette\Utils\Json;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[CoversNothing]
class GitlabWebhookTest extends AbstractFunctionalTestCase
{
    private RemoteEventHandler&MockObject $eventHandler;

    protected function setUp(): void
    {
        parent::setUp();
        $this->eventHandler = $this->createMock(RemoteEventHandler::class);
    }

    /**
     * @throws Exception
     */
    public function testWebhook(): void
    {
        // setup repository en property
        $repository = Assert::notNull(self::getService(RepositoryRepository::class)->findOneBy(['name' => 'repository']));
        $property   = (new RepositoryProperty('gitlab-project-id', '123'))->setRepository($repository);
        self::getService(RepositoryPropertyRepository::class)->save($property, true);

        // setup mock
        $this->eventHandler->expects($this->once())->method('handle');
        self::getContainer()->set(RemoteEventHandler::class, $this->eventHandler);

        // setup request
        $body   = ['object_kind' => 'Push Hook', 'event_type' => 'Push Hook', 'project_id' => 123];
        $server = ['HTTP_X_GITLAB_EVENT' => 'Push Hook', 'HTTP_X_GITLAB_TOKEN' => '123test'];

        // execute
        $this->client->request(Request::METHOD_POST, '/webhook/gitlab', [], [], $server, Json::encode($body));
        self::assertResponseIsSuccessful();

        $response = $this->client->getResponse();
        static::assertInstanceOf(Response::class, $response);
        static::assertSame(Response::HTTP_ACCEPTED, $response->getStatusCode());
    }

    /**
     * @inheritDoc
     */
    protected function getFixtures(): array
    {
        return [RepositoryFixtures::class];
    }
}
