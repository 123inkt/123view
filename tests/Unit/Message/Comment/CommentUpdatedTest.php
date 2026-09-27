<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentUpdated;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentUpdated::class)]
class CommentUpdatedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentUpdated(5, 6, 7, 'file', 'message', CommentModificationEnum::Local, 'original');

        static::assertSame(CommentModificationEnum::Local, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-updated',
            5,
            [
                'commentId'       => 6,
                'file'            => 'file',
                'message'         => 'message',
                'originalComment' => 'original',
                'modifiedBy'      => 'local',
            ]
        );
        static::assertCommentEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
