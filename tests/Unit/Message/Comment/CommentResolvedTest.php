<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentResolved::class)]
class CommentResolvedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(
            new CommentResolved(5, 6, 7, 'file'),
            'comment-resolved',
            5,
            ['commentId' => 6, 'file' => 'file', 'resolvedByUserId' => 7]
        );
        static::assertCommentEvent(new CommentResolved(5, 6, 7, 'file'), 6);
        static::assertUserAware(new CommentResolved(5, 6, 7, 'file'), 7);
    }
}
