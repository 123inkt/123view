<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CommentModificationEnum;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\CommentFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CommentFactory::class)]
class CommentFactoryTest extends AbstractTestCase
{
    public function testCreateComment(): void
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;
        $event->discussionId                  = 'discussion';
        $event->note                          = 'Please update this line.';
        $event->position                      = new Position();
        $event->position->oldPath             = 'old.php';
        $event->position->newPath             = 'new.php';
        $event->position->oldLine             = 10;
        $event->position->newLine             = 12;

        $repository = new Repository()->setDisplayName('Repository');
        $review     = new CodeReview()->setId(123)->setRepository($repository);
        $revision   = new Revision()->setCommitHash('commitsha');
        $revision->setReview($review);
        $user = new User()->setId(456)->setName('User')->setEmail('user@example.com');

        $comment = new CommentFactory()->create($event, $user, $revision, 'new.php');

        static::assertSame('new.php', $comment->getFilePath());
        static::assertSame('Please update this line.', $comment->getMessage());
        static::assertSame($review, $comment->getReview());
        static::assertSame($user, $comment->getUser());
        static::assertSame('7:discussion:42', $comment->getExtReferenceId());
        static::assertSame(CommentModificationEnum::Gitlab, $comment->getModifiedBy());
        static::assertSame('old.php', $comment->getLineReference()->oldPath);
        static::assertSame('new.php', $comment->getLineReference()->newPath);
        static::assertSame(10, $comment->getLineReference()->line);
        static::assertSame(12, $comment->getLineReference()->lineAfter);
        static::assertSame('commitsha', $comment->getLineReference()->headSha);
        static::assertSame($comment, $review->getComments()->first());
    }

    public function testUsesNewLineWhenOldLineIsNull(): void
    {
        $event                                = new NoteEvent();
        $event->id                            = 42;
        $event->mergeRequest                  = new MergeRequest();
        $event->mergeRequest->mergeRequestIId = 7;
        $event->discussionId                  = 'discussion';
        $event->note                          = 'Comment';
        $event->position                      = new Position();
        $event->position->newPath             = 'new.php';
        $event->position->newLine             = 12;

        $review   = new CodeReview();
        $revision = new Revision()->setCommitHash('commitsha');
        $revision->setReview($review);

        $comment = new CommentFactory()->create($event, new User(), $revision, 'new.php');

        static::assertSame(12, $comment->getLineReference()->line);
        static::assertSame(12, $comment->getLineReference()->lineAfter);
    }
}
