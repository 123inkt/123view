<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Mcp;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Service\Ai\AddCommentService;
use DR\Review\Service\Ai\Mcp\CodeReviewAddCommentTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\SecurityBundle\Security;
use Throwable;

#[CoversClass(CodeReviewAddCommentTool::class)]
class CodeReviewAddCommentToolTest extends AbstractTestCase
{
    private Security&MockObject            $security;
    private AddCommentService&MockObject   $commentService;
    private CodeReviewAddCommentTool       $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->security       = $this->createMock(Security::class);
        $this->commentService = $this->createMock(AddCommentService::class);
        $this->tool           = new CodeReviewAddCommentTool($this->security, $this->commentService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldAddCommentSuccessfully(): void
    {
        $user = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->commentService->expects($this->once())->method('addComment')
            ->with($user, 456, 'src/Service/Test.php', 25, 'Needs refactoring', 'return $value;')
            ->willReturn(new Comment()->setId(789));

        $result = ($this->tool)(456, 'src/Service/Test.php', 25, 'Needs refactoring', 'return $value;');
        static::assertSame(789, $result);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldAddCommentWithoutSuggestion(): void
    {
        $user = new User();

        $this->security->expects($this->once())->method('getUser')->willReturn($user);
        $this->commentService->expects($this->once())->method('addComment')
            ->with($user, 123, 'src/file.php', 10, 'comment message', null)
            ->willReturn(new Comment()->setId(321));

        $result = ($this->tool)(123, 'src/file.php', 10, 'comment message', null);
        static::assertSame(321, $result);
    }
}
