<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
use DR\Review\Entity\User\User;
use DR\Review\Repository\Mcp\CodeReviewRepository;
use DR\Review\Service\Ai\Tool\GetCommentsTool;
use DR\Review\Tests\AbstractTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;

#[CoversClass(GetCommentsTool::class)]
class GetCommentsToolTest extends AbstractTestCase
{
    private CodeReviewRepository&MockObject $reviewRepository;
    private GetCommentsTool                 $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->reviewRepository = $this->createMock(CodeReviewRepository::class);
        $this->tool             = new GetCommentsTool($this->reviewRepository);
    }

    public function testInvokeShouldThrowExceptionWhenReviewNotFound(): void
    {
        $this->reviewRepository->expects($this->once())->method('find')->with(123)->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Code review not found');
        ($this->tool)(123);
    }

    public function testInvokeShouldReturnEmptyArrayForReviewWithNoComments(): void
    {
        $review = new CodeReview();
        $this->reviewRepository->expects($this->once())->method('find')->with(456)->willReturn($review);

        $result = ($this->tool)(456);
        static::assertSame([], $result);
    }

    public function testInvokeShouldReturnMappedComments(): void
    {
        $user = new User()->setId(7)->setName('Jane Doe')->setEmail('jane@example.com');

        $comment = new Comment()
            ->setId(42)
            ->setMessage('Needs refactoring')
            ->setState('open')
            ->setLineReference(new LineReference(oldPath: 'src/old.php', newPath: 'src/new.php', lineAfter: 25))
            ->setUser($user)
            ->setCreateTimestamp(1700000000);

        $review = new CodeReview();
        $review->getComments()->add($comment);

        $this->reviewRepository->expects($this->once())->method('find')->with(456)->willReturn($review);

        $result = ($this->tool)(456);

        static::assertCount(1, $result);
        static::assertSame([
            'commentId' => 42,
            'message'   => 'Needs refactoring',
            'state'     => 'open',
            'file'      => 'src/new.php',
            'line'      => 25,
            'author'    => [
                'userId' => 7,
                'name'   => 'Jane Doe',
                'email'  => 'jane@example.com',
            ],
            'createdAt' => date('c', 1700000000),
        ], $result[0]);
    }

    public function testInvokeShouldFallBackToOldPathWhenNewPathIsNull(): void
    {
        $user = new User()->setId(1)->setName('John')->setEmail('john@example.com');

        $comment = new Comment()
            ->setId(1)
            ->setMessage('comment')
            ->setState('open')
            ->setLineReference(new LineReference(oldPath: 'src/old.php', newPath: null, lineAfter: 10))
            ->setUser($user)
            ->setCreateTimestamp(1700000000);

        $review = new CodeReview();
        $review->getComments()->add($comment);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);

        $result = ($this->tool)(1);
        static::assertSame('src/old.php', $result[0]['file']);
    }

    public function testInvokeShouldReturnMultipleComments(): void
    {
        $user = new User()->setId(1)->setName('John')->setEmail('john@example.com');

        $comment1 = new Comment()
            ->setId(1)->setMessage('first')->setState('open')
            ->setLineReference(new LineReference(newPath: 'a.php', lineAfter: 1))
            ->setUser($user)->setCreateTimestamp(1000);

        $comment2 = new Comment()
            ->setId(2)->setMessage('second')->setState('resolved')
            ->setLineReference(new LineReference(newPath: 'b.php', lineAfter: 5))
            ->setUser($user)->setCreateTimestamp(2000);

        $review = new CodeReview();
        $review->getComments()->add($comment1);
        $review->getComments()->add($comment2);

        $this->reviewRepository->expects($this->once())->method('find')->willReturn($review);

        $result = ($this->tool)(1);
        static::assertCount(2, $result);
        static::assertSame(1, $result[0]['commentId']);
        static::assertSame(2, $result[1]['commentId']);
    }
}
