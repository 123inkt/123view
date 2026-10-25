<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyAdded::class)]
class CommentReplyAddedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentReplyAdded(5, 6, 7, 'message', 'file');

        static::assertSame(CommentModificationEnum::Local, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-reply-added',
            5,
            ['commentId' => 6, 'message' => 'message', 'file' => 'file', 'modifiedBy' => 'local']
        );
        static::assertCommentReplyEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
