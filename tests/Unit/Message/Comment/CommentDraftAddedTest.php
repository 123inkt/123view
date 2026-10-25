<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentDraftAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentDraftAdded::class)]
class CommentDraftAddedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentDraftAdded(5, 6, 7, 'file', 'message'),
            'comment-draft-added',
            5,
            ['commentId' => 6, 'file' => 'file', 'message' => 'message']
        );
        static::assertCommentEvent(new CommentDraftAdded(5, 6, 7, 'file', 'message'), 6);
        static::assertUserAware(new CommentDraftAdded(5, 6, 7, 'file', 'message'), 7);
    }
}
