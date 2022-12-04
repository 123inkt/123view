<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use DR\GitCommitNotification\Message\CodeReviewAwareInterface;
use DR\GitCommitNotification\Repository\Webhook\WebhookActivityRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

class WebhookExecutionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly WebhookActivityRepository $activityRepository, private readonly HttpClientInterface $httpClient)
    {
    }

    public function execute(Webhook $webhook, CodeReviewAwareInterface $event): WebhookActivity
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
    private function tryExecute(Webhook $webhook, WebhookActivity $activity, CodeReviewAwareInterface $event): void
    {
        $client = $this->httpClient;
        // @codeCoverageIgnoreStart
        if ($webhook->getRetries() > 0) {
            $client = new RetryableHttpClient($this->httpClient, maxRetries: $webhook->getRetries(), logger: $this->logger);
        }
        // @codeCoverageIgnoreEnd

        // setup request body
        $requestBody = ['name' => $event->getName(), 'payload' => $event->getPayload()];
        $activity->setRequestHeaders(['Authorization' => ''] + $webhook->getHeaders());
        $activity->setRequest(json_encode($requestBody, JSON_THROW_ON_ERROR));

        // setup request options
        $options = [
            'headers'     => $webhook->getHeaders(),
            'timeout'     => 60,
            'verify_peer' => $webhook->isVerifySsl(),
            'verify_host' => $webhook->isVerifySsl(),
            'json'        => $requestBody
        ];

        // execute request
        $response = $client->request('POST', (string)$webhook->getUrl(), $options);

        $activity->setStatusCode($response->getStatusCode());
        $activity->setResponseHeaders($response->getHeaders(false));
        $activity->setResponse($response->getContent(false));
    }
}
