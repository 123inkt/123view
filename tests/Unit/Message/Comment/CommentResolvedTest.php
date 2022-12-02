<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Message\Comment;

use DR\GitCommitNotification\Message\Comment\CommentResolved;
use DR\GitCommitNotification\Tests\Unit\Message\AbstractMessageEventTestCase;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Message\Comment\CommentResolved
 */
class CommentResolvedTest extends AbstractMessageEventTestCase
{
    /**
     * @covers ::__construct
     * @covers ::getName
     * @covers ::getReviewId
     * @covers ::getPayload
     */
    public function testAccessors(): void
    {
        static::assertCodeReviewEvent(new CommentResolved(5, 6, 7), 'comment-resolved', 5, ['comment-id' => 6, 'resolved-by-user-id' => 7]);
        static::assertCommentEvent(new CommentResolved(5, 6, 7), 6);
    }
}
