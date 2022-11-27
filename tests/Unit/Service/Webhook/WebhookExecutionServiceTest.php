<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\Webhook;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Webhook\WebhookActivityRepository;
use DR\GitCommitNotification\Service\Webhook\WebhookExecutionService;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\Webhook\WebhookExecutionService
 * @covers ::__construct
 */
class WebhookExecutionServiceTest extends AbstractTestCase
{
    private WebhookActivityRepository&MockObject $activityRepository;
    private HttpClientInterface&MockObject       $httpClient;
    private WebhookExecutionService              $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->activityRepository = $this->createMock(WebhookActivityRepository::class);
        $this->httpClient         = $this->createMock(HttpClientInterface::class);
        $this->service            = new WebhookExecutionService($this->activityRepository, $this->httpClient);
    }

    /**
     * @covers ::execute
     * @covers ::tryExecute
     */
    public function testExecuteSuccessfulWithoutRetry(): void
    {
        $event = $this->createMock(WebhookEventInterface::class);
        $event->method('getName')->willReturn('name');
        $event->method('getPayload')->willReturn(['payload']);

        $webhook = new Webhook();
        $webhook->setUrl('url');
        $webhook->setRetries(0);
        $webhook->setHeaders(['headers' => 'headers']);
        $webhook->setVerifySsl(true);

        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->with(false)->willReturn(['response' => 'headers']);
        $response->method('getContent')->with(false)->willReturn('content');

        $this->activityRepository->expects(self::once())->method('save')->with(self::isInstanceOf(WebhookActivity::class), true);
        $this->httpClient->expects(self::once())
            ->method('request')
            ->with(
                'POST',
                'url',
                [
                    'headers' => ['headers' => 'headers'],
                    'timeout' => 60,
                    'verify_peer' => true,
                    'verify_host' => true,
                    'json' => ['name' => 'name', 'payload' => ['payload']],
                ]
            )
            ->willReturn($response);

        $this->service->execute($webhook, $event);
    }

    /**
     * @covers ::execute
     * @covers ::tryExecute
     */
    public function testExecuteFailure(): void
    {
        $event = $this->createMock(WebhookEventInterface::class);
        $event->method('getName')->willReturn('name');
        $event->method('getPayload')->willReturn(['payload']);

        $webhook = new Webhook();
        $webhook->setUrl('url');
        $webhook->setRetries(0);
        $webhook->setHeaders(['headers' => 'headers']);
        $webhook->setVerifySsl(true);

        $this->httpClient->expects(self::once())
            ->method('request')
            ->willThrowException($this->createMock(TransportExceptionInterface::class));

        $activity = $this->service->execute($webhook, $event);
        static::assertSame(500, $activity->getStatusCode());
    }
}
