<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentRemoved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentRemoved::class)]
class CommentRemovedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentRemoved(5, 6, 7, 'file', 'message', null),
            'comment-removed',
            5,
            ['commentId' => 6, 'file' => 'file', 'message' => 'message']
        );
        static::assertCommentEvent(new CommentRemoved(5, 6, 7, 'file', 'message', null), 6);
        static::assertUserAware(new CommentRemoved(5, 6, 7, 'file', 'message', null), 7);
    }
}
