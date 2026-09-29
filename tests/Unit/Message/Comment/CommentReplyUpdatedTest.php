<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentReplyUpdated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentReplyUpdated::class)]
class CommentReplyUpdatedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentReplyUpdated(5, 6, 7, 'original');

        static::assertSame(CommentModificationEnum::Local, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-reply-updated',
            5,
            ['commentId' => 6, 'originalComment' => 'original', 'modifiedBy' => 'local']
        );
        static::assertCommentReplyEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
