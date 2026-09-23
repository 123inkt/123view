<?php

declare(strict_types=1);

namespace DR\Review\Tests\Unit\ApiPlatform\StateProcessor;

use ApiPlatform\Metadata\Post;
use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\ApiPlatform\Factory\CommentOutputFactory;
use DR\Review\ApiPlatform\Input\CreateCommentInput;
use DR\Review\ApiPlatform\Output\CommentOutput;
use DR\Review\ApiPlatform\StateProcessor\CreateCommentProcessor;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentStateEnum;
use DR\Review\Entity\Review\CommentTagEnum;
use DR\Review\Entity\Review\CommentTypeEnum;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\Comment\CommentLocationValidator;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Service\User\UserEntityProvider;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[CoversClass(CreateCommentProcessor::class)]
class CreateCommentProcessorTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CodeReviewRepository&MockObject $reviewRepository;
    private CommentRepository&MockObject $commentRepository;
    private CodeReviewRevisionService&MockObject $revisionService;
    private CommentLocationValidator&MockObject  $locationResolver;
    private LineReferenceFactory&MockObject      $lineReferenceFactory;
    private UserEntityProvider&MockObject $userProvider;
    private CommentOutputFactory&MockObject $outputFactory;
    private CreateCommentProcessor $processor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository      = $this->createMock(CodeReviewRepository::class);
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->revisionService       = $this->createMock(CodeReviewRevisionService::class);
        $this->locationResolver      = $this->createMock(CommentLocationValidator::class);
        $this->lineReferenceFactory  = $this->createMock(LineReferenceFactory::class);
        $this->userProvider          = $this->createMock(UserEntityProvider::class);
        $this->outputFactory         = $this->createMock(CommentOutputFactory::class);
        $this->processor             = new CreateCommentProcessor(
            $this->reviewRepository,
            $this->commentRepository,
            $this->revisionService,
            $this->locationResolver,
            $this->lineReferenceFactory,
            $this->userProvider,
            $this->outputFactory,
        );
    }

    public function testCreatesComment(): void
    {
        $review     = new CodeReview()->setId(20);
        $user       = new User()->setId(10);
        $repository = new Repository();
        $first      = new Revision()->setRepository($repository)->setCommitHash('first');
        $latest     = new Revision()->setRepository($repository)->setCommitHash('latest');
        $reference  = new LineReference(null, 'src/Foo.php', 42, 0, 42, 'latest');
        $output     = static::createStub(CommentOutput::class);
        $input      = $this->input('  Please extract this condition.  ', ' src/Foo.php ', 42, CommentTagEnum::Suggestion->value);

        $this->reviewRepository->expects($this->once())->method('find')->with(20)->willReturn($review);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$first, $latest]);
        $this->locationResolver->expects($this->once())->method('validate')->with($review, 'src/Foo.php', 42);
        $this->lineReferenceFactory
            ->expects($this->once())
            ->method('createFromReview')
            ->with($review, 'src/Foo.php', 42, 'latest')
            ->willReturn($reference);
        $this->commentRepository
            ->expects($this->once())
            ->method('save')
            ->with(self::isInstanceOf(Comment::class), true);
        $this->outputFactory
            ->expects($this->once())
            ->method('create')
            ->with(self::callback(static function (Comment $comment) use ($review, $user, $reference): bool {
                self::assertSame($review, $comment->getReview());
                self::assertSame($user, $comment->getUser());
                self::assertSame('Please extract this condition.', $comment->getMessage());
                self::assertSame('src/Foo.php', $comment->getFilePath());
                self::assertEquals($reference, $comment->getLineReference());
                self::assertSame(CommentTagEnum::Suggestion, $comment->getTag());
                self::assertSame(CommentTypeEnum::Final, $comment->getType());
                self::assertSame(CommentStateEnum::Open, $comment->getState());
                self::assertSame($comment->getCreateTimestamp(), $comment->getUpdateTimestamp());

                return true;
            }))
            ->willReturn($output);

        self::assertSame($output, $this->processor->process($input, new Post(), ['reviewId' => '20']));
        self::assertCount(1, $review->getComments());
        $comment = $review->getComments()->first();
        self::assertInstanceOf(Comment::class, $comment);
        self::assertSame(self::time(), $comment->getCreateTimestamp());
    }

    public function testUsesRouteReviewAndUser(): void
    {
        $review = new CodeReview()->setId(20);
        $user   = new User()->setId(10);
        $input  = $this->input('Comment', 'src/Foo.php', 1, null);

        $this->reviewRepository->expects($this->once())->method('find')->with(20)->willReturn($review);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn($user);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([
            new Revision()->setRepository(new Repository())->setCommitHash('sha'),
        ]);
        $this->locationResolver->expects($this->once())->method('validate');
        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->commentRepository->expects($this->once())->method('save');
        $this->outputFactory->expects($this->once())->method('create')->willReturn(static::createStub(CommentOutput::class));

        $this->processor->process($input, new Post(), ['reviewId' => '20']);

        $comment = $review->getComments()->first();
        self::assertInstanceOf(Comment::class, $comment);
        self::assertSame($review, $comment->getReview());
        self::assertSame($user, $comment->getUser());
    }

    public function testRejectsMissingReview(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(20)->willReturn(null);
        $this->revisionService->expects($this->never())->method('getRevisions');
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->locationResolver->expects($this->never())->method('validate');
        $this->lineReferenceFactory->expects($this->never())->method('createFromReview');
        $this->commentRepository->expects($this->never())->method('save');
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(NotFoundHttpException::class);
        $this->processor->process($this->input('Comment', 'src/Foo.php', 1, null), new Post(), ['reviewId' => '20']);
    }

    public function testRejectsNoRevision(): void
    {
        $review = new CodeReview()->setId(20);
        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([]);
        $this->userProvider->expects($this->never())->method('getCurrentUser');
        $this->locationResolver->expects($this->never())->method('validate');
        $this->commentRepository->expects($this->never())->method('save');
        $this->lineReferenceFactory->expects($this->never())->method('createFromReview');
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor->process($this->input('Comment', 'src/Foo.php', 1, null), new Post(), ['reviewId' => '20']);
    }

    public function testRejectsInvalidLocation(): void
    {
        $review = new CodeReview()->setId(20);
        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([
            new Revision()->setRepository(new Repository())->setCommitHash('sha'),
        ]);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn(new User());
        $this->locationResolver->expects($this->once())->method('validate')->willThrowException(new UnprocessableEntityHttpException());
        $this->lineReferenceFactory->expects($this->never())->method('createFromReview');
        $this->commentRepository->expects($this->never())->method('save');
        $this->outputFactory->expects($this->never())->method('create');

        $this->expectException(UnprocessableEntityHttpException::class);
        $this->processor->process($this->input('Comment', 'src/Missing.php', 1, null), new Post(), ['reviewId' => '20']);
    }

    public function testProcessAcceptsExplicitNullTag(): void
    {
        $review = new CodeReview()->setId(20);
        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);
        $this->revisionService->expects($this->once())->method('getRevisions')->willReturn([
            new Revision()->setRepository(new Repository())->setCommitHash('sha'),
        ]);
        $this->userProvider->expects($this->once())->method('getCurrentUser')->willReturn(new User());
        $this->locationResolver->expects($this->once())->method('validate');
        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->commentRepository->expects($this->once())->method('save');
        $this->outputFactory->expects($this->once())->method('create')->willReturn(static::createStub(CommentOutput::class));

        $this->processor->process($this->input('Comment', 'src/Foo.php', 1, null), new Post(), ['reviewId' => '20']);

        $comment = $review->getComments()->first();
        self::assertInstanceOf(Comment::class, $comment);
        self::assertNull($comment->getTag());
    }

    private function input(string $message, string $filepath, ?int $line, ?string $tag): CreateCommentInput
    {
        $input           = new CreateCommentInput();
        $input->message  = $message;
        $input->filepath = $filepath;
        $input->line     = $line;
        $input->tag      = $tag;

        return $input;
    }
}
