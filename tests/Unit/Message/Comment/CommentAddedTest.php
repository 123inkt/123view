<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Message\Comment;

use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Tests\Unit\Message\AbstractMessageEventTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentAdded::class)]
class CommentAddedTest extends AbstractMessageEventTestCase
{
    public function testAccessors(): void
    {
        $event = new CommentAdded(5, 6, 7, 'file', 'message', CommentModificationEnum::Gitlab);

        static::assertSame(CommentModificationEnum::Gitlab, $event->getModifiedBy());
        static::assertCodeReviewEvent(
            $event,
            'comment-added',
            5,
            ['commentId' => 6, 'file' => 'file', 'message' => 'message', 'modifiedBy' => 'gitlab']
        );
        static::assertCommentEvent($event, 6);
        static::assertUserAware($event, 7);
    }
}
