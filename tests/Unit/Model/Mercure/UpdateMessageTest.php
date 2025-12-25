<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Model\Mercure;

use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\Http\Message\UriInterface;

#[CoversClass(UpdateMessage::class)]
class UpdateMessageTest extends AbstractTestCase
{
    public function testJsonSerialize(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('__toString')->willReturn('https://example.com/review/123');

        $message = new UpdateMessage(
            eventId: 1,
            userId: 2,
            reviewId: 3,
            eventName: 'review.created',
            title: 'Test Title',
            message: 'Test Message',
            url: $uri
        );

        static::assertSame(1, $message->eventId);
        static::assertSame(2, $message->userId);
        static::assertSame(3, $message->reviewId);
        static::assertSame('review.created', $message->eventName);
        static::assertSame('Test Title', $message->title);
        static::assertSame('Test Message', $message->message);
        static::assertSame($uri, $message->url);

        $expected = [
            'eventId'   => 1,
            'userId'    => 2,
            'reviewId'  => 3,
            'eventName' => 'review.created',
            'title'     => 'Test Title',
            'message'   => 'Test Message',
            'url'       => 'https://example.com/review/123',
        ];

        static::assertSame($expected, $message->jsonSerialize());
    }

    public function testJsonSerializeWithNullReviewId(): void
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('__toString')->willReturn('https://example.com/commits');

        $message = new UpdateMessage(
            eventId: 10,
            userId: 20,
            reviewId: null,
            eventName: 'commit.notification',
            title: 'Commit Title',
            message: 'Commit Message',
            url: $uri
        );

        static::assertNull($message->reviewId);

        $expected = [
            'eventId'   => 10,
            'userId'    => 20,
            'reviewId'  => null,
            'eventName' => 'commit.notification',
            'title'     => 'Commit Title',
            'message'   => 'Commit Message',
            'url'       => 'https://example.com/commits',
        ];

        static::assertSame($expected, $message->jsonSerialize());
    }
}
