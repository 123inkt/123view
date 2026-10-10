<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\ViewModel\App\Comment;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\ViewModel\App\Comment\CommentOverviewItem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentOverviewItem::class)]
class CommentOverviewItemTest extends AbstractTestCase
{
    public function testComment(): void
    {
        $comment = new Comment();
        $item    = new CommentOverviewItem($comment);

        static::assertFalse($item->isReply);
        static::assertSame($comment, $item->content);
        static::assertSame($comment, $item->comment);
    }

    public function testReply(): void
    {
        $comment = new Comment();
        $reply   = new CommentReply();
        $reply->setComment($comment);
        $item = new CommentOverviewItem($reply);

        static::assertTrue($item->isReply);
        static::assertSame($reply, $item->content);
        static::assertSame($comment, $item->comment);
    }
}
