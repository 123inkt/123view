<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\RemoteEvent\Gitlab\NoteEvent;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\User\User;
use DR\Review\Model\Api\Gitlab\MergeRequest;
use DR\Review\Model\Api\Gitlab\Position;
use DR\Review\Model\Api\Gitlab\User as GitlabUser;
use DR\Review\Model\Webhook\Gitlab\NoteEvent;
use DR\Review\Service\RemoteEvent\Gitlab\NoteEvent\NoteEventHandlerLogger;
use DR\Review\Tests\AbstractTestCase;
use DR\Utils\Assert;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Psr\Log\LoggerInterface;

#[CoversClass(NoteEventHandlerLogger::class)]
class NoteEventHandlerLoggerTest extends AbstractTestCase
{
    private LoggerInterface&MockObject $messageLogger;
    private NoteEventHandlerLogger     $eventLogger;

    protected function setUp(): void
    {
        parent::setUp();
        $this->messageLogger = $this->createMock(LoggerInterface::class);
        $this->eventLogger   = new NoteEventHandlerLogger();
        $this->eventLogger->setLogger($this->messageLogger);
    }

    public function testLogCommentAlreadyExists(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: comment already exists in 123view',
                ['discussionId' => 'discussion', 'message' => 'Comment']
            );

        $this->eventLogger->logCommentAlreadyExists($event);
    }

    public function testLogCommentReplyAlreadyExists(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: comment reply already exists in 123view',
                ['discussionId' => 'discussion', 'message' => 'Comment']
            );

        $this->eventLogger->logCommentAlreadyExists($event, true);
    }

    public function testLogCommentNotFound(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: comment not found in 123view',
                ['referenceId' => '7:discussion:42', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logCommentNotFound($event, '7:discussion:42');
    }

    public function testLogCommentUnchanged(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: comment message is unchanged in 123view',
                ['referenceId' => '7:discussion:42', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logCommentUnchanged($event, '7:discussion:42');
    }

    public function testLogCommentReplyUnchanged(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: reply message is unchanged in 123view',
                ['referenceId' => '7:discussion:42', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logCommentUnchanged($event, '7:discussion:42', true);
    }

    public function testLogCommentUpdated(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: updated comment in 123view',
                ['referenceId' => '7:discussion:42', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logCommentUpdated($event, '7:discussion:42');
    }

    public function testLogCommentReplyUpdated(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: updated reply in 123view',
                ['referenceId' => '7:discussion:42', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logCommentUpdated($event, '7:discussion:42', true);
    }

    public function testLogUserNotFound(): void
    {
        $event             = $this->createEvent();
        $gitlabUser        = new GitlabUser();
        $gitlabUser->email = 'user@example.com';
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: user {email} not found in 123view',
                ['email' => 'user@example.com', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logUserNotFound($event, $gitlabUser);
    }

    public function testLogRepositoryNotFound(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: repository {id} doesn\'t exist or is inactive in 123view',
                ['id' => 321, 'discussionId' => 'discussion']
            );

        $this->eventLogger->logRepositoryNotFound($event);
    }

    public function testLogRevisionsNotFound(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: no revisions found for branch {name}',
                ['name' => 'feature', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logRevisionsNotFound($event);
    }

    public function testLogRevisionUsesNewPath(): void
    {
        $event = $this->createEvent();
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: no revision matching file {file}',
                ['file' => 'new.php', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logRevisionForFilenameNotFound($event);
    }

    public function testLogRevisionUsesOldPath(): void
    {
        $event                                     = $this->createEvent();
        Assert::notNull($event->position)->newPath = null;
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: no revision matching file {file}',
                ['file' => 'old.php', 'discussionId' => 'discussion']
            );

        $this->eventLogger->logRevisionForFilenameNotFound($event);
    }

    public function testLogCommentAddedSuccess(): void
    {
        $event      = $this->createEvent();
        $repository = new Repository()->setDisplayName('Review repository');
        $review     = new CodeReview()->setProjectId(42)->setRepository($repository);
        $user       = new User()->setName('Review user');
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: creating comment for {file} on {repository}: {review} by {user}',
                [
                    'file'         => 'new.php',
                    'repository'   => 'Review repository',
                    'review'       => 'CR-42',
                    'user'         => 'Review user',
                    'discussionId' => 'discussion',
                ]
            );

        $this->eventLogger->logCommentAddedSuccess($event, $review, $user);
    }

    public function testLogCommentReplyAddedSuccess(): void
    {
        $event      = $this->createEvent();
        $repository = new Repository()->setDisplayName('Review repository');
        $review     = new CodeReview()->setProjectId(42)->setRepository($repository);
        $user       = new User()->setName('Review user');
        $this->messageLogger->expects($this->once())
            ->method('info')
            ->with(
                'NoteEventHandler: creating comment reply on {repository}: {review} by {user}',
                [
                    'repository'   => 'Review repository',
                    'review'       => 'CR-42',
                    'user'         => 'Review user',
                    'discussionId' => 'discussion',
                ]
            );

        $this->eventLogger->logCommentReplyAddedSuccess($event, $review, $user);
    }

    private function createEvent(): NoteEvent
    {
        $event                             = new NoteEvent();
        $event->projectId                  = 321;
        $event->mergeRequest               = new MergeRequest();
        $event->mergeRequest->sourceBranch = 'feature';
        $event->discussionId               = 'discussion';
        $event->note                       = 'Comment';
        $event->position                   = new Position();
        $event->position->newPath          = 'new.php';
        $event->position->oldPath          = 'old.php';
        $event->user                       = new GitlabUser();
        $event->user->id                   = 123;

        return $event;
    }
}
