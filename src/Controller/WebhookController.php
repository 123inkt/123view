<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
use DR\GitCommitNotification\Repository\Webhook\WebhookActivityRepository;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\RetryableHttpClient;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class WebhookController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly WebhookActivityRepository $activityRepository)
    {
    }

    /**
     *
    review-created
    review-closed
    review-opened
    review-accepted
    review-rejected
    reviewer-added
    reviewer-removed
    revision-added
     */

    /**
     * @throws Throwable
     */
    #[Route('/webhook/{id<\d+>}', self::class, methods: ['GET'])]
    #[Entity('webhook')]
    public function __invoke(Webhook $webhook): JsonResponse
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

        return new JsonResponse($data);
    }
}
