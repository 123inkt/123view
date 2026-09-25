<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\Factory;

use DR\Review\ApiPlatform\Factory\CommentReplyOutputFactory;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyOutputFactory::class)]
class CommentReplyOutputFactoryTest extends AbstractTestCase
{
    public function testCreateMapsReplyFields(): void
    {
        $reply = new CommentReply();
        $reply->setId(123);
        $reply->setComment(new Comment()->setId(20));
        $reply->setUser(new User()->setId(10));
        $reply->setMessage('Reply text');
        $reply->setTag(CommentTagEnum::Suggestion);
        $reply->setCreateTimestamp(1234567890);
        $reply->setUpdateTimestamp(1234567891);

        $output = new CommentReplyOutputFactory()->create($reply);

        static::assertSame(123, $output->id);
        static::assertSame(20, $output->commentId);
        static::assertSame(10, $output->userId);
        static::assertSame('Reply text', $output->message);
        static::assertSame('suggestion', $output->tag);
        static::assertSame(1234567890, $output->createdAt->getTimestamp());
        static::assertSame(1234567891, $output->updatedAt->getTimestamp());
    }

    public function testCreateMapsNullableTag(): void
    {
        $reply = new CommentReply();
        $reply->setId(123);
        $reply->setComment(new Comment()->setId(20));
        $reply->setUser(new User()->setId(10));
        $reply->setMessage('Reply text');
        $reply->setTag(null);
        $reply->setCreateTimestamp(1000);
        $reply->setUpdateTimestamp(2000);

        static::assertNull(new CommentReplyOutputFactory()->create($reply)->tag);
    }
}
