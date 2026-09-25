<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Mercure;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Mercure\UpdateMessage;
use DR\Review\Service\Mercure\MessagePublisher;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Http\Message\UriInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

#[CoversClass(MessagePublisher::class)]
class MessagePublisherTest extends AbstractTestCase
{
    private HubInterface&MockObject $mercureHub;
    private MessagePublisher        $publisher;

    public function setUp(): void
    {
        parent::setUp();
        $this->mercureHub = $this->createMock(HubInterface::class);
        $this->publisher  = new MessagePublisher($this->mercureHub);
        $this->publisher->setLogger($this->logger);
    }

    public function testPublishToReview(): void
    {
        $uri = static::createStub(UriInterface::class);
        $uri->method('__toString')->willReturn('https://example.com/review/123');

        $message = new UpdateMessage(
            eventId: 1,
            userId: 2,
            reviewId: 123,
            eventName: 'review.created',
            title: 'Test Title',
            message: 'Test Message',
            url: $uri
        );

        $review = new CodeReview();
        $review->setId(123);

        $this->mercureHub->expects($this->once())
            ->method('publish')
            ->with(self::callback(static function (Update $update): bool {
                static::assertSame(['/review/123'], $update->getTopics());
                self::assertTrue($update->isPrivate());

                return true;
            }));

        $this->publisher->publishToReview($message, $review);
    }

    public function testPublishToUsersSingleUser(): void
    {
        $uri = static::createStub(UriInterface::class);
        $uri->method('__toString')->willReturn('https://example.com/user/456');

        $message = new UpdateMessage(
            eventId: 10,
            userId: 456,
            reviewId: null,
            eventName: 'notification',
            title: 'User Title',
            message: 'User Message',
            url: $uri
        );

        $user = new User();
        $user->setId(456);

        $this->mercureHub->expects($this->once())
            ->method('publish')
            ->with(self::callback(static function (Update $update): bool {
                static::assertSame(['/user/456'], $update->getTopics());
                self::assertTrue($update->isPrivate());

                return true;
            }));

        $this->publisher->publishToUsers($message, $user);
    }
}
