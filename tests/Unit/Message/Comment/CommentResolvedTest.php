<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentResolved;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentResolved::class)]
class CommentResolvedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentResolved(5, 6, 7, 'file');

        static::assertSame(CommentModificationEnum::Local, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-resolved',
            5,
            ['commentId' => 6, 'file' => 'file', 'resolvedByUserId' => 7, 'modifiedBy' => 'local']
        );
        static::assertCommentEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
