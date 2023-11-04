<?php
declare(strict_types=1);

namespace DR\Review\Service\Webhook\Receive\Gitlab;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class WebhookRequestDeserializer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const DESERIALIZE_CONTEXT = [
        DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES            => true
    ];

    public function __construct(private readonly SerializerInterface $objectSerializer)
    {
    }

    public function deserialize(string $eventType, string $data): ?PushEvent
    {
        $eventClass = self::getEventClass($eventType);
        if ($eventClass === null) {
            $this->logger?->info('WebhookRequestDeserializer: Unsupported event type: {eventType}', ['eventType' => $eventType]);

            return null;
        }

        $this->logger?->info('WebhookRequestDeserializer: Deserializing event type: {eventType}', ['eventType' => $eventType]);

        return $this->objectSerializer->deserialize($data, $eventClass, JsonEncoder::FORMAT, self::DESERIALIZE_CONTEXT);
    }

    /**
     * Convert Gitlab webhook event to related data class
     * @link https://docs.gitlab.com/ee/user/project/integrations/webhook_events.html
     */
    private static function getEventClass(string $eventType): ?string
    {
        return match ($eventType) {
            'Push Hook' => PushEvent::class,
            default     => null,
        };
    }
}
