<?php
declare(strict_types=1);

namespace DR\Review\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
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
    public function denormalize(string $eventType, array $data): PushEvent|MergeRequestEvent|null
    {
        $eventClass = self::getEventClass($eventType);
        if ($eventClass === null) {
            $this->logger?->info('RemoteEventPayloadDenormalizer: Unsupported event type: {eventType}', ['eventType' => $eventType]);

            return null;
        }

        $this->logger?->info('RemoteEventPayloadDenormalizer: Denormalizing event type: {eventType}', ['eventType' => $eventType]);

        try {
            return $this->objectDenormalizer->denormalize($data, $eventClass, null, self::DENORMALIZE_CONTEXT);
        } catch (ExceptionInterface $exception) {
            throw $this->handleException($eventType, $exception);
        }
    }

    private function handleException(string $eventType, ExceptionInterface $exception): ExceptionInterface
    {
        $context = [
            'eventType' => $eventType,
            'exception' => $exception
        ];

        if ($exception instanceof PartialDenormalizationException) {
            $context['errors'] = array_map(static fn($error) => $error->getMessage(), $exception->getErrors());
        }

        $this->logger?->error('Failed to denormalize {eventType}', $context);

        return $exception;
    }

    /**
     * Convert Gitlab webhook event to related data class
     * @link https://docs.gitlab.com/ee/user/project/integrations/webhook_events.html
     */
    private static function getEventClass(string $eventType): ?string
    {
        return match ($eventType) {
            'Push Hook'          => PushEvent::class,
            'Merge Request Hook' => MergeRequestEvent::class,
            default              => null,
        };
    }
}
