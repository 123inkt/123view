<?php
declare(strict_types=1);

namespace DR\Review\Controller\Webhook;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\Webhook\Receive\WebhookEventHandler;
use DR\Utils\Assert;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class GitlabWebhookController
{
    public function __construct(private readonly SerializerInterface $objectSerializer, private readonly WebhookEventHandler $eventHandler)
    {
    }

    #[Route('/webhook/gitlab', name: self::class, methods: 'POST')]
    #[IsGranted('ROLE_GITLAB_WEBHOOK')]
    public function __invoke(): Response
    {
        $data = Assert::notFalse(file_get_contents('php://input'));

        $event = $this->objectSerializer->deserialize(
            $data,
            PushEvent::class,
            JsonEncoder::FORMAT,
            [
                DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
                AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES            => true
            ]
        );

        $this->eventHandler->handle($event);

        return new Response('OK');
    }
}
