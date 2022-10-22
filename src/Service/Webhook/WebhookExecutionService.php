<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Service\Webhook;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Throwable;

class WebhookExecutionService implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct()
    {
    }

    /**
     * @throws Throwable
     */
    public function execute(Webhook $webhook, )
    {
        $options = [
            'headers'     => $webhook->getHeaders(),
            'verify_peer' => $webhook->isVerifySsl(),
            'verify_host' => $webhook->isVerifySsl(),
        ];

        // setup request
        $client = HttpClient::create($options);
        if ($webhook->getRetries() > 0) {
            $client = new RetryableHttpClient($client, maxRetries: $webhook->getRetries(), logger: $this->logger);
        }

        // execute request
        $requestBody = ['event' => 'review-created', 'payload' => 5];
        $response    = $client->request('POST', (string)$webhook->getUrl(), ['json' => $requestBody]);

        // get response
        $data = $response->toArray();

        $requestHeaders = $webhook->getHeaders();
        if (isset($requestHeaders['Authorization'])) {
            $requestHeaders['Authorization'] = '';
        }

        // register attempt
        $activity = new WebhookActivity();
        $activity->setWebhook($webhook);
        $activity->setStatusCode($response->getStatusCode());
        $activity->setRequestHeaders($requestHeaders);
        $activity->setRequest(json_encode($requestBody, JSON_THROW_ON_ERROR));
        $activity->setResponseHeaders($response->getHeaders());
        $activity->setResponse($response->getContent());
        $activity->setCreateTimestamp(time());

        $this->activityRepository->save($activity, true);
    }
}
