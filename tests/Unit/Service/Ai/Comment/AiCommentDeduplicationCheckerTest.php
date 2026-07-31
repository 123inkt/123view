<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Comment;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\Review\LineReferenceStateEnum;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Ai\Comment\AiCommentDeduplicationChecker;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(AiCommentDeduplicationChecker::class)]
class AiCommentDeduplicationCheckerTest extends AbstractTestCase
{
    private CommentRepository&MockObject     $commentRepository;
    private AiCommentDeduplicationChecker    $checker;

    public function setUp(): void
    {
        parent::setUp();
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->checker            = new AiCommentDeduplicationChecker($this->commentRepository);
    }

    private function createComment(LineReference $lineReference, string $message): Comment
    {
        $comment = new Comment();
        $comment->setLineReference($lineReference);
        $comment->setMessage($message);

        return $comment;
    }

    public function testIsDuplicateShouldReturnFalseWhenNoExistingComments(): void
    {
        $review        = new CodeReview()->setId(1);
        $lineReference = new LineReference('src/Foo.php', 'src/Foo.php', 10, 1, 10, 'abc123', LineReferenceStateEnum::Added);

        $this->commentRepository->expects($this->once())->method('findByReview')->with($review, ['src/Foo.php'])->willReturn([]);

        static::assertFalse($this->checker->isDuplicate($review, 'src/Foo.php', $lineReference, 'A problem'));
    }

    public function testIsDuplicateShouldReturnTrueForSameLineAndNormalizedMessage(): void
    {
        $review        = new CodeReview()->setId(1);
        $lineReference = new LineReference('src/Foo.php', 'src/Foo.php', 10, 1, 10, 'abc123', LineReferenceStateEnum::Added);
        $existing       = $this->createComment($lineReference, "A   Problem\n");

        $this->commentRepository->expects($this->once())->method('findByReview')->willReturn([$existing]);

        static::assertTrue($this->checker->isDuplicate($review, 'src/Foo.php', $lineReference, 'a problem'));
    }

    public function testIsDuplicateShouldReturnFalseForDifferentLineReference(): void
    {
        $review          = new CodeReview()->setId(1);
        $lineReference   = new LineReference('src/Foo.php', 'src/Foo.php', 10, 1, 10, 'abc123', LineReferenceStateEnum::Added);
        $otherLineRef    = new LineReference('src/Foo.php', 'src/Foo.php', 20, 1, 20, 'abc123', LineReferenceStateEnum::Added);
        $existing        = $this->createComment($otherLineRef, 'A problem');

        $this->commentRepository->expects($this->once())->method('findByReview')->willReturn([$existing]);

        static::assertFalse($this->checker->isDuplicate($review, 'src/Foo.php', $lineReference, 'A problem'));
    }

    public function testIsDuplicateShouldReturnFalseForDifferentMessage(): void
    {
        $review         = new CodeReview()->setId(1);
        $lineReference  = new LineReference('src/Foo.php', 'src/Foo.php', 10, 1, 10, 'abc123', LineReferenceStateEnum::Added);
        $existing       = $this->createComment($lineReference, 'A different problem');

        $this->commentRepository->expects($this->once())->method('findByReview')->willReturn([$existing]);

        static::assertFalse($this->checker->isDuplicate($review, 'src/Foo.php', $lineReference, 'A problem'));
    }
}
