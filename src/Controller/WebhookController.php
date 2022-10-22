<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Controller;

use DR\GitCommitNotification\Entity\Webhook\Webhook;
use DR\GitCommitNotification\Message\ReviewAccepted;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Throwable;

class WebhookController implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    public function __construct(private readonly MessageBusInterface $bus)
    {
    }

    /**
     * V review-created
     * review-closed
     * review-opened
     * review-accepted
     * review-rejected
     * reviewer-added
     * reviewer-removed
     * V review-revision-added
     */

    /**
     * @throws Throwable
     */
    #[Route('/webhook/{id<\d+>}', self::class, methods: ['GET'])]
    #[Entity('webhook')]
    public function __invoke(Webhook $webhook): JsonResponse
    {
        $this->bus->dispatch(new ReviewAccepted(11707));

        return new JsonResponse('dispatched');
    }
}
