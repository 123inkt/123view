<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Entity\Review\Comment;
use DR\Review\Model\Api\Gitlab\Discussion;
use DR\Review\Model\Api\Gitlab\Note;
use DR\Review\Repository\Review\CommentRepository;
use DR\Review\Service\Api\Gitlab\Discussions;
use DR\Review\Service\Api\Gitlab\GitlabCommentResolver;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(GitlabCommentResolver::class)]
class GitlabCommentResolverTest extends AbstractTestCase
{
    private Discussions&MockObject       $discussions;
    private CommentRepository&MockObject $commentRepository;
    private GitlabCommentResolver        $resolver;

    protected function setUp(): void
    {
        parent::setUp();
        $this->discussions       = $this->createMock(Discussions::class);
        $this->commentRepository = $this->createMock(CommentRepository::class);
        $this->resolver          = new GitlabCommentResolver($this->discussions, $this->commentRepository);
    }

    /**
     * @throws Throwable
     */
    public function testResolveCommentStart(): void
    {
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(123, 456, 'discussion')
            ->willReturn($this->createDiscussion(789));
        $this->commentRepository->expects($this->never())->method('findOneBy');

        static::assertSame([false, null], $this->resolver->resolve(123, 456, 'discussion', 789));
    }

    /**
     * @throws Throwable
     */
    public function testResolveReplyWithExistingComment(): void
    {
        $comment = new Comment();
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(123, 456, 'discussion')
            ->willReturn($this->createDiscussion(789, 987));
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '456:discussion:789'])
            ->willReturn($comment);

        static::assertSame([true, $comment], $this->resolver->resolve(123, 456, 'discussion', 987));
    }

    /**
     * @throws Throwable
     */
    public function testResolveReplyWithoutExistingComment(): void
    {
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(123, 456, 'discussion')
            ->willReturn($this->createDiscussion(789, 987));
        $this->commentRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['extReferenceId' => '456:discussion:789'])
            ->willReturn(null);

        static::assertSame([true, null], $this->resolver->resolve(123, 456, 'discussion', 987));
    }

    /**
     * @throws Throwable
     */
    public function testResolveMissingDiscussion(): void
    {
        $this->discussions->expects($this->once())
            ->method('getDiscussion')
            ->with(123, 456, 'discussion')
            ->willReturn(null);
        $this->commentRepository->expects($this->never())->method('findOneBy');

        static::assertSame([false, null], $this->resolver->resolve(123, 456, 'discussion', 789));
    }

    private function createDiscussion(int ...$noteIds): Discussion
    {
        $discussion     = new Discussion();
        $discussion->id = 'discussion';
        foreach ($noteIds as $noteId) {
            $note     = new Note();
            $note->id = $noteId;
            $discussion->addNote($note);
        }

        return $discussion;
    }
}
