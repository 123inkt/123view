<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Message\RevisionAddedMessage;
use DR\GitCommitNotification\Service\Webhook\WebhookExecutionService;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class WebhookController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly WebhookExecutionService $executionService)
    {
    }

    /**
     * review-created
     * review-closed
     * review-opened
     * review-accepted
     * review-rejected
     * reviewer-added
     * reviewer-removed
     * revision-added
     */

    /**
     * @throws Throwable
     */
    #[Route('/webhook/{id<\d+>}', self::class, methods: ['GET'])]
    #[Entity('webhook')]
    public function __invoke(Webhook $webhook): JsonResponse
    {
        $activity = $this->executionService->execute($webhook, new RevisionAddedMessage(5));

        return new JsonResponse($activity);
    }
}
