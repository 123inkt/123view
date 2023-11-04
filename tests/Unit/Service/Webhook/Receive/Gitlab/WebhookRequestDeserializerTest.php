<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Webhook\Receive\Gitlab;

use DR\Review\Model\Webhook\Gitlab\PushEvent;
use DR\Review\Service\Webhook\Receive\Gitlab\WebhookRequestDeserializer;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Serializer\SerializerInterface;

#[CoversClass(WebhookRequestDeserializer::class)]
class WebhookRequestDeserializerTest extends AbstractTestCase
{
    private SerializerInterface&MockObject $objectSerializer;
    private WebhookRequestDeserializer     $deserializer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->objectSerializer = $this->createMock(SerializerInterface::class);
        $this->deserializer     = new WebhookRequestDeserializer($this->objectSerializer);
    }

    public function testDeserializeUnknownEvent(): void
    {
        $this->objectSerializer->expects(self::never())->method('deserialize');
        static::assertNull($this->deserializer->deserialize('Unknown Event', 'data'));
    }

    public function testDeserializeKnownEvent(): void
    {
        $event = new PushEvent();

        $this->objectSerializer->expects(self::once())
            ->method('deserialize')
            ->with('data', PushEvent::class, 'json', ['collect_denormalization_errors' => true, 'allow_extra_attributes' => true])
            ->willReturn($event);

        static::assertSame($event, $this->deserializer->deserialize('Push Hook', 'data'));
    }
}
