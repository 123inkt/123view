<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai;

use DR\PHPUnitExtensions\Symfony\ClockTestTrait;
use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Ai\AddCommentService;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Service\CodeReview\LineReferenceFactory;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AddCommentService::class)]
class AddCommentServiceTest extends AbstractTestCase
{
    use ClockTestTrait;

    private CodeReviewRepository&MockObject      $repository;
    private CommentRepository&MockObject         $commentRepository;
    private CodeReviewRevisionService&MockObject $reviewRevisionService;
    private LineReferenceFactory&MockObject      $lineReferenceFactory;
    private AddCommentService                    $service;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository            = $this->createMock(CodeReviewRepository::class);
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->reviewRevisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->lineReferenceFactory  = $this->createMock(LineReferenceFactory::class);
        $this->service               = new AddCommentService(
            $this->logger,
            $this->repository,
            $this->commentRepository,
            $this->reviewRevisionService,
            $this->lineReferenceFactory
        );
    }

    public function testAddCommentShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->lineReferenceFactory->expects($this->never())->method(static::anything());
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->commentRepository->expects($this->never())->method('save');
        $this->reviewRevisionService->expects($this->never())->method('getRevisions');

        $this->expectException(CodeReviewNotFoundException::class);
        $this->service->addComment(new User(), 123, 'src/file.php', 10, 'comment', null);
    }

    public function testAddCommentShouldAddCommentWithoutSuggestion(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision()->setRepository($repositoryEntity)->setCommitHash('abc123');
        $review           = new CodeReview()->setId(456);
        $user             = new User()->setId(1);
        $lineReference    = new LineReference('src/Service/Test.php', 'src/Service/Test.php', 23, 2, 25, 'abc123', LineReferenceStateEnum::Added);

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->lineReferenceFactory->expects($this->once())
            ->method('createFromReview')
            ->with($review, 'src/Service/Test.php', 25, 'abc123')
            ->willReturn($lineReference);
        $this->commentRepository->expects($this->once())->method('save')->with(self::isInstanceOf(Comment::class), true);

        $this->service->addComment($user, 456, 'src/Service/Test.php', 25, 'Needs refactoring', null);

        static::assertCount(1, $review->getComments());
        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame('src/Service/Test.php', $comment->getFilePath());
        static::assertSame('Needs refactoring', $comment->getMessage());
        static::assertSame($user, $comment->getUser());
        static::assertSame($review, $comment->getReview());
        static::assertEquals($lineReference, $comment->getLineReference());
    }

    public function testAddCommentShouldAppendCodeSuggestion(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision()->setRepository($repositoryEntity)->setCommitHash('def456');
        $review           = new CodeReview()->setId(1);

        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->commentRepository->expects($this->once())->method('save');

        $this->service->addComment(new User(), 1, 'file.php', 5, 'Use this instead', 'return $value;');

        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame("Use this instead\n\n```\nreturn \$value;\n```", $comment->getMessage());
    }

    public function testAddCommentShouldSkipEmptyCodeSuggestion(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision()->setRepository($repositoryEntity)->setCommitHash('abc123');
        $review           = new CodeReview()->setId(1);

        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->commentRepository->expects($this->once())->method('save');

        $this->service->addComment(new User(), 1, 'file.php', 5, 'Just a comment', '');

        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame('Just a comment', $comment->getMessage());
    }

    public function testAddCommentShouldReplaceKissEmoticonInMessage(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision()->setRepository($repositoryEntity)->setCommitHash('abc123');
        $review           = new CodeReview()->setId(1);

        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->commentRepository->expects($this->once())->method('save');

        $this->service->addComment(new User(), 1, 'file.php', 5, '**Note:**bold text', null);

        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame('**Note**bold text', $comment->getMessage());
    }

    public function testAddCommentShouldSetTimestamps(): void
    {
        $repositoryEntity = new Repository();
        $revision         = new Revision()->setRepository($repositoryEntity)->setCommitHash('abc123');
        $review           = new CodeReview()->setId(1);

        $this->lineReferenceFactory->expects($this->once())->method('createFromReview')->willReturn(new LineReference());
        $this->repository->expects($this->once())->method('find')->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->willReturn([$revision]);
        $this->commentRepository->expects($this->once())->method('save');

        $this->service->addComment(new User(), 1, 'file.php', 5, 'comment', null);

        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame(self::now()->getTimestamp(), $comment->getCreateTimestamp());
        static::assertSame(self::now()->getTimestamp(), $comment->getUpdateTimestamp());
    }
}
