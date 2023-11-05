<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\RemoteEvent\Gitlab\RemoteEventPayloadDenormalizer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
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
    public function testDeserializeKnownEvent(): void
    {
        $event = new PushEvent();

        $this->objectDenormalizer->expects(self::once())
            ->method('denormalize')
            ->with(['data'], PushEvent::class, null, ['collect_denormalization_errors' => true, 'allow_extra_attributes' => true])
            ->willReturn($event);

        static::assertSame($event, $this->denormalizer->denormalize('Push Hook', ['data']));
    }
}
