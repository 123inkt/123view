<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Ai\Tool;

use DR\Review\Entity\User\User;
use DR\Review\Repository\User\UserRepository;
use DR\Review\Service\Ai\AddCommentService;
use DR\Review\Service\Ai\Tool\CodeReviewAddCommentTool;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Throwable;

#[CoversClass(CodeReviewAddCommentTool::class)]
class CodeReviewAddCommentToolTest extends AbstractTestCase
{
    private UserRepository&MockObject   $userRepository;
    private AddCommentService&MockObject $commentService;
    private CodeReviewAddCommentTool    $tool;

    public function setUp(): void
    {
        parent::setUp();
        $this->userRepository = $this->createMock(UserRepository::class);
        $this->commentService = $this->createMock(AddCommentService::class);
        $this->tool           = new CodeReviewAddCommentTool(1, $this->userRepository, $this->commentService);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldAddCommentSuccessfully(): void
    {
        $user = new User()->setId(1);

        $this->userRepository->expects($this->once())->method('find')->with(1)->willReturn($user);
        $this->commentService->expects($this->once())->method('addComment')
            ->with($user, 456, 'src/Service/Test.php', 25, 'This needs refactoring', 'return $value;');

        $result = ($this->tool)(456, 'src/Service/Test.php', 25, 'This needs refactoring', 'return $value;');
        static::assertSame('Comment added successfully.', $result);
    }

    /**
     * @throws Throwable
     */
    public function testInvokeShouldAddCommentWithoutSuggestion(): void
    {
        $user = new User()->setId(1);

        $this->userRepository->expects($this->once())->method('find')->with(1)->willReturn($user);
        $this->commentService->expects($this->once())->method('addComment')
            ->with($user, 123, 'src/file.php', 10, 'comment message', null);

        $result = ($this->tool)(123, 'src/file.php', 10, 'comment message', null);
        static::assertSame('Comment added successfully.', $result);
    }
}
