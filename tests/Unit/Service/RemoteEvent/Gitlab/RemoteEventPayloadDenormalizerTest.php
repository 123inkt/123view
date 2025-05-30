<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\MergeRequestEvent;
use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\RemoteEvent\Gitlab\RemoteEventPayloadDenormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Exception\PartialDenormalizationException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

#[CoversClass(RemoteEventPayloadDenormalizer::class)]
class RemoteEventPayloadDenormalizerTest extends AbstractTestCase
{
    private DenormalizerInterface&MockObject $objectDenormalizer;
    private RemoteEventPayloadDenormalizer   $denormalizer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectDenormalizer = $this->createMock(DenormalizerInterface::class);
        $this->denormalizer       = new RemoteEventPayloadDenormalizer($this->objectDenormalizer);
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializeUnknownEvent(): void
    {
        $this->objectDenormalizer->expects(self::never())->method('denormalize');
        static::assertNull($this->denormalizer->denormalize('Unknown Event', []));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializePushHook(): void
    {
        $event = new PushEvent();

        $this->objectDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['data'], PushEvent::class, null, ['collect_denormalization_errors' => true, 'allow_extra_attributes' => true])
            ->willReturn($event);

        static::assertSame($event, $this->denormalizer->denormalize('Push Hook', ['data']));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializeMergeRequest(): void
    {
        $event = new MergeRequestEvent();

        $this->objectDenormalizer->expects($this->once())
            ->method('denormalize')
            ->with(['data'], MergeRequestEvent::class, null, ['collect_denormalization_errors' => true, 'allow_extra_attributes' => true])
            ->willReturn($event);

        static::assertSame($event, $this->denormalizer->denormalize('Merge Request Hook', ['data']));
    }

    /**
     * @throws ExceptionInterface
     */
    public function testDeserializeDenormalizeException(): void
    {
        $exception = new PartialDenormalizationException([], [new NotNormalizableValueException('error')]);

        $this->objectDenormalizer->expects($this->once())->method('denormalize')->willThrowException($exception);

        $this->expectException(PartialDenormalizationException::class);
        $this->denormalizer->denormalize('Merge Request Hook', ['data']);
    }
}
