<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\CodeReviewActivity;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Message\Comment\CommentAdded;
use DR\Review\Message\Comment\CommentReplyAdded;
use DR\Review\Message\Review\ReviewAccepted;
use DR\Review\Message\Reviewer\ReviewerAdded;
use DR\Review\Message\Reviewer\ReviewerStateChanged;
use DR\Review\Message\Revision\ReviewRevisionAdded;
use DR\Review\Repository\Revision\RevisionRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter;
use DR\Review\Service\CodeReview\Activity\CodeReviewActivityVariableFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @coversDefaultClass \DR\Review\Service\CodeReview\Activity\CodeReviewActivityFormatter
 * @covers ::__construct
 */
class CodeReviewActivityFormatterTest extends AbstractTestCase
{
    private TranslatorInterface&MockObject $translator;
    private UserRepository&MockObject      $userRepository;
    private RevisionRepository&MockObject  $revisionRepository;
    private CodeReviewActivityFormatter    $formatter;

    public function setUp(): void
    {
        parent::setUp();
        $this->translator         = $this->createMock(TranslatorInterface::class);
        $this->userRepository     = $this->createMock(UserRepository::class);
        $this->revisionRepository = $this->createMock(RevisionRepository::class);
        $urlGenerator             = $this->createMock(UrlGeneratorInterface::class);
        $urlGenerator->method('generate')->willReturn('url');
        $this->formatter = new CodeReviewActivityFormatter(
            $this->translator,
            $this->userRepository,
            $this->revisionRepository,
            new CodeReviewActivityVariableFactory($urlGenerator),
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

        $this->formatter->format($activity, $user);
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

        $this->formatter->format($activity, $user);
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

        $this->formatter->format($activity, $user);
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

        $this->formatter->format($activity, $user);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatRevisionAddedEventWithAbsentRevision(): void
    {
        $user = new User();
        $user->setId(456);

        $activity = new CodeReviewActivity();
        $activity->setEventName(ReviewRevisionAdded::NAME);
        $activity->setData(['revisionId' => 789, 'title' => 'title']);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.review.revision.added', ['username' => 'app', 'revision' => 'title'])
            ->willReturnArgument(0);
        $this->userRepository->expects(self::never())->method('find');
        $this->revisionRepository->expects(self::once())->method('find')->with(789)->willReturn(null);

        $this->formatter->format($activity, $user);
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

        $review = new CodeReview();
        $review->setId(123);

        $comment = new Comment();
        $comment->setId(789);
        $comment->setReview($review);
        $comment->setFilePath('filepath');
        $review->getComments()->set(789, $comment);

        $activity = new CodeReviewActivity();
        $activity->setReview($review);
        $activity->setEventName(CommentAdded::NAME);
        $activity->setData(['commentId' => 789]);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.comment.added', ['username' => 'app', 'file' => '<a href="url#focus:comment:789">filepath</a>'])
            ->willReturnArgument(0);

        $this->formatter->format($activity, $user);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatDeletedCommentEvent(): void
    {
        $user = new User();
        $user->setId(456);

        $activity = new CodeReviewActivity();
        $activity->setEventName(CommentAdded::NAME);
        $activity->setData(['commentId' => 789, 'file' => 'filepath']);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.comment.added', ['username' => 'app', 'file' => 'filepath'])
            ->willReturnArgument(0);

        $this->formatter->format($activity, $user);
    }

    /**
     * @covers ::format
     * @covers ::addCustomParams
     * @covers ::getTranslationId
     */
    public function testFormatCommentReplyEvent(): void
    {
        $user = new User();
        $user->setId(456);

        $activity = new CodeReviewActivity();
        $activity->setEventName(CommentReplyAdded::NAME);
        $activity->setData(['commentId' => 789, 'file' => 'filepath', 'message' => 'message']);

        $this->translator->expects(self::once())
            ->method('trans')
            ->with('timeline.comment.reply.added', ['username' => 'app', 'message' => 'message'])
            ->willReturnArgument(0);

        $this->formatter->format($activity, $user);
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

        static::assertNull($this->formatter->format($activity, $user));
    }
}
