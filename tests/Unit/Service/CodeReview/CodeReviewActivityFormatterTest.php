<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Service\CodeReview;

use DR\GitCommitNotification\Entity\Review\CodeReviewActivity;
use DR\GitCommitNotification\Entity\Review\Comment;
use DR\GitCommitNotification\Entity\Review\Revision;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Message\Comment\CommentAdded;
use DR\GitCommitNotification\Message\Review\ReviewAccepted;
use DR\GitCommitNotification\Message\Reviewer\ReviewerAdded;
use DR\GitCommitNotification\Message\Reviewer\ReviewerStateChanged;
use DR\GitCommitNotification\Message\Revision\ReviewRevisionAdded;
use DR\GitCommitNotification\Repository\Review\CommentRepository;
use DR\GitCommitNotification\Repository\Review\RevisionRepository;
use DR\GitCommitNotification\Repository\User\UserRepository;
use DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityFormatter;
use DR\GitCommitNotification\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Service\CodeReview\CodeReviewActivityFormatter
 * @covers ::__construct
 */
class CodeReviewActivityFormatterTest extends AbstractTestCase
{
    private TranslatorInterface&MockObject $translator;
    private UserRepository&MockObject      $userRepository;
    private RevisionRepository&MockObject  $revisionRepository;
    private CommentRepository&MockObject   $commentRepository;
    private CodeReviewActivityFormatter    $formatter;

    public function setUp(): void
    {
        parent::setUp();
        $this->translator         = $this->createMock(TranslatorInterface::class);
        $this->userRepository     = $this->createMock(UserRepository::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $this->commentRepository  = $this->createMock(CommentRepository::class);
        $this->formatter          = new CodeReviewActivityFormatter(
            $this->translator,
            $this->userRepository,
            $this->revisionRepository,
            $this->commentRepository,
            'app'
        );
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatDefaultEvent(): void
    {
        $user = new User();
        $user->setId(456);
        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewAccepted::NAME);
        $activity->setUser($user);

        $this->translator->expects(self::exactly(2))
            ->method('trans')
            ->withConsecutive(['you'], ['timeline.review.accepted'])
            ->willReturnArgument(0);
        $this->userRepository->expects(self::never())->method('find');
        $this->revisionRepository->expects(self::never())->method('find');

        $this->formatter->format($user, $activity);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatReviewerEvent(): void
    {
        $user = new User();
        $user->setId(456);
        $reviewerUser = new User();
        $reviewerUser->setName('reviewer');
        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewerAdded::NAME);
        $activity->setData(['userId' => 789]);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.reviewer.added.by')
            ->willReturnArgument(0);
        $this->userRepository->expects(self::once())->method('find')->with(789)->willReturn($reviewerUser);
        $this->revisionRepository->expects(self::never())->method('find');

        $this->formatter->format($user, $activity);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatReviewerStateChangedEvent(): void
    {
        $user = new User();
        $user->setId(456);
        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewerStateChanged::NAME);
        $activity->setData(['newState' => "accepted"]);
        $activity->setUser((new User())->setName('user'));

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.reviewer.accepted', ['username' => 'user'])
            ->willReturnArgument(0);
        $this->userRepository->expects(self::never())->method('find');
        $this->revisionRepository->expects(self::never())->method('find');

        $this->formatter->format($user, $activity);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatRevisionAddedEvent(): void
    {
        $user = new User();
        $user->setId(456);

        $revision = new Revision();
        $revision->setCommitHash('hash');
        $revision->setTitle('title');

        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewRevisionAdded::NAME);
        $activity->setData(['revisionId' => 789]);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.review.revision.added', ['username' => 'app', 'revision' => 'hash - title'])
            ->willReturnArgument(0);
        $this->userRepository->expects(self::never())->method('find');
        $this->revisionRepository->expects(self::once())->method('find')->with(789)->willReturn($revision);

        $this->formatter->format($user, $activity);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatCommentEvent(): void
    {
        $user = new User();
        $user->setId(456);

        $comment = new Comment();
        $comment->setFilePath('filepath');

        $activity = new CodeReviewActivity();
        $activity->setEventName(CommentAdded::NAME);
        $activity->setData(['commentId' => 789]);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.comment.added', ['username' => 'app', 'file' => 'filepath'])
            ->willReturnArgument(0);
        $this->commentRepository->expects(self::once())->method('find')->with(789)->willReturn($comment);

        $this->formatter->format($user, $activity);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatUnknownEvent(): void
    {
        $user = new User();
        $user->setId(456);

        $activity = new CodeReviewActivity();
        $activity->setEventName('foobar');

        $this->translator->expects(self::never())->method('trans');
        $this->userRepository->expects(self::never())->method('find');
        $this->revisionRepository->expects(self::never())->method('find');

        static::assertNull($this->formatter->format($user, $activity));
    }
}
