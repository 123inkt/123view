<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentUnresolved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentUnresolved::class)]
class CommentUnresolvedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentUnresolved(5, 6, 7, 'file');

        static::assertSame(CommentModificationEnum::Local, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-unresolved',
            5,
            ['commentId' => 6, 'file' => 'file', 'unresolvedByUserId' => 7, 'modifiedBy' => 'local']
        );
        static::assertCommentEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
