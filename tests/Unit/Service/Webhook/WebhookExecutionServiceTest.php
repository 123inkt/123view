<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook;

use DR\Review\Entity\Webhook\Webhook;
use DR\Review\Entity\Webhook\WebhookActivity;
use DR\Review\Message\CodeReviewAwareInterface;
use DR\Review\Repository\Webhook\WebhookActivityRepository;
use DR\Review\Service\Webhook\WebhookExecutionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

#[CoversClass(WebhookExecutionService::class)]
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

    public function testExecuteSuccessfulWithoutRetry(): void
    {
        $event = static::createStub(CodeReviewAwareInterface::class);
        $event->method('getName')->willReturn('name');
        $event->method('getPayload')->willReturn(['payload']);

        $webhook = new Webhook();
        $webhook->setUrl('url');
        $webhook->setRetries(0);
        $webhook->setHeaders(['headers' => 'headers']);
        $webhook->setVerifySsl(true);

        $response = static::createStub(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(200);
        $response->method('getHeaders')->with(false)->willReturn(['response' => 'headers']);
        $response->method('getContent')->with(false)->willReturn('content');

        $this->activityRepository->expects($this->once())->method('save')->with(self::isInstanceOf(WebhookActivity::class), true);
        $this->httpClient->expects($this->once())
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

    public function testExecuteFailure(): void
    {
        $event = static::createStub(CodeReviewAwareInterface::class);
        $event->method('getName')->willReturn('name');
        $event->method('getPayload')->willReturn(['payload']);

        $webhook = new Webhook();
        $webhook->setUrl('url');
        $webhook->setRetries(0);
        $webhook->setHeaders(['headers' => 'headers']);
        $webhook->setVerifySsl(true);

        $this->httpClient->expects($this->once())
            ->method('request')
            ->willThrowException(static::createStub(TransportExceptionInterface::class));
        $this->activityRepository->expects($this->never())->method('save');

        $activity = $this->service->execute($webhook, $event);
        static::assertSame(500, $activity->getStatusCode());
    }
}
