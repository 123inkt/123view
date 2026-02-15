<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Repository\Repository;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Entity\User\User;
use DR\Review\Exception\Ai\CodeReviewNotFoundException;
use DR\Review\Repository\Review\CodeReviewRepository;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Ai\Tool\CodeReviewAddCommentTool;
use DR\Review\Service\CodeReview\CodeReviewRevisionService;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Clock\MockClock;

#[CoversClass(CodeReviewAddCommentTool::class)]
class CodeReviewAddCommentToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject      $repository;
    private UserRepository&MockObject            $userRepository;
    private CommentRepository&MockObject         $commentRepository;
    private CodeReviewRevisionService&MockObject $reviewRevisionService;
    private CodeReviewAddCommentTool             $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->repository            = $this->createMock(CodeReviewRepository::class);
        $this->userRepository        = $this->createMock(UserRepository::class);
        $this->commentRepository     = $this->createMock(CommentRepository::class);
        $this->reviewRevisionService = $this->createMock(CodeReviewRevisionService::class);
        $this->tool                  = new CodeReviewAddCommentTool(
            1,
            $this->logger,
            $this->repository,
            $this->userRepository,
            $this->commentRepository,
            $this->reviewRevisionService
        );
        $this->tool->setClock(new MockClock());
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->repository->expects($this->once())->method('find')->with(123)->willReturn(null);
        $this->userRepository->expects($this->never())->method('find');
        $this->commentRepository->expects($this->never())->method('save');
        $this->reviewRevisionService->expects($this->never())->method('getRevisions');

        $this->expectException(CodeReviewNotFoundException::class);
        ($this->tool)(123, 'src/file.php', 10, 'comment message', null);
    }

    public function testInvokeShouldAddCommentSuccessfully(): void
    {
        $repositoryEntity = new Repository();
        $revision         = (new Revision())->setRepository($repositoryEntity)->setCommitHash('abc123');

        $review = new CodeReview();
        $review->getRevisions()->add($revision);

        $user = (new User())->setId(1);

        $this->repository->expects($this->once())->method('find')->with(456)->willReturn($review);
        $this->reviewRevisionService->expects($this->once())->method('getRevisions')->with($review)->willReturn([$revision]);
        $this->userRepository->expects($this->once())->method('find')->with(1)->willReturn($user);
        $this->commentRepository->expects($this->once())->method('save')->with(self::isInstanceOf(Comment::class), true);

        $result = ($this->tool)(456, 'src/Service/Test.php', 25, 'This needs refactoring', 'return $value;');
        static::assertSame('Comment added successfully.', $result);
        static::assertCount(1, $review->getComments());

        $comment = $review->getComments()->first();
        static::assertInstanceOf(Comment::class, $comment);
        static::assertSame('src/Service/Test.php', $comment->getFilePath());
        static::assertStringContainsString('This needs refactoring', $comment->getMessage());
        static::assertStringContainsString('return $value;', $comment->getMessage());
    }
}
