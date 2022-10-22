<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Entity\Webhook\WebhookActivity;
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
        $response = $client->request('POST', (string)$webhook->getUrl(), ['json' => ['event' => 'review-created', 'payload' => 5]]);

        // get response
        $data = $response->toArray();

        // register attempt

        return new JsonResponse($data);
    }
}
