<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class RemoteEventPayloadDenormalizer implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    private const DENORMALIZE_CONTEXT = [
        DenormalizerInterface::COLLECT_DENORMALIZATION_ERRORS => true,
        AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES            => true
    ];

    public function __construct(private readonly DenormalizerInterface $objectDenormalizer)
    {
    }

    /**
     * @param array<int|string, mixed> $data
     *
     * @throws ExceptionInterface
     */
    public function denormalize(string $eventType, array $data): ?PushEvent
    {
        $eventClass = self::getEventClass($eventType);
        if ($eventClass === null) {
            $this->logger?->info('RemoteEventPayloadDenormalizer: Unsupported event type: {eventType}', ['eventType' => $eventType]);

            return null;
        }

        $this->logger?->info('RemoteEventPayloadDenormalizer: Denormalizing event type: {eventType}', ['eventType' => $eventType]);

        return $this->objectDenormalizer->denormalize($data, $eventClass, null, self::DENORMALIZE_CONTEXT);
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