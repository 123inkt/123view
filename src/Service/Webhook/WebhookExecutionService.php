<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use DR\GitCommitNotification\Message\WebhookEventInterface;
use DR\GitCommitNotification\Repository\Webhook\WebhookActivityRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Throwable;

class WebhookExecutionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly WebhookActivityRepository $activityRepository)
    {
    }

    public function execute(Webhook $webhook, WebhookEventInterface $event): WebhookActivity
    {
        // track request/response
        $activity = new WebhookActivity();
        $activity->setWebhook($webhook);
        $activity->setCreateTimestamp(time());

        try {
            $this->tryExecute($webhook, $activity, $event);
        } catch (Throwable $exception) {
            $this->logger?->error($exception->getMessage(), ['exception' => $exception]);
            $activity->setStatusCode(500);
            $activity->setResponseHeaders([]);
            $activity->setResponse($exception->getMessage());
        } finally {
            $this->activityRepository->save($activity, true);
        }

        return $activity;
    }

    /**
     * @throws Throwable
     */
    private function tryExecute(Webhook $webhook, WebhookActivity $activity, WebhookEventInterface $event): void
    {
        $options = [
            'headers'     => $webhook->getHeaders(),
            'timeout'     => 60,
            'verify_peer' => $webhook->isVerifySsl(),
            'verify_host' => $webhook->isVerifySsl(),
        ];

        // setup client
        $client = HttpClient::create($options);
        if ($webhook->getRetries() > 0) {
            $client = new RetryableHttpClient($client, maxRetries: $webhook->getRetries(), logger: $this->logger);
        }

        // setup request
        $requestBody = ['name' => $event->getName(), 'payload' => $event->getPayload()];
        $activity->setRequestHeaders(['Authorization' => ''] + $webhook->getHeaders());
        $activity->setRequest(json_encode($requestBody, JSON_THROW_ON_ERROR));

        // execute request
        $response = $client->request('POST', (string)$webhook->getUrl(), ['json' => $requestBody]);

        $activity->setStatusCode($response->getStatusCode());
        $activity->setResponseHeaders($response->getHeaders());
        $activity->setResponse($response->getContent());
    }
}
