<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\CommentPreviewController;
use DR\GitCommitNotification\Entity\User\User;
use DR\GitCommitNotification\Request\Comment\CommentPreviewRequest;
use DR\GitCommitNotification\Service\CodeReview\Comment\CommentMentionService;
use DR\GitCommitNotification\Service\Markdown\MarkdownService;
use DR\GitCommitNotification\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;

/**
 * @coversDefaultClass \DR\GitCommitNotification\Controller\App\Review\Comment\CommentPreviewController
 * @covers ::__construct
 */
class CommentPreviewControllerTest extends AbstractControllerTestCase
{
    private MarkdownService&MockObject       $markdownService;
    private CommentMentionService&MockObject $mentionService;

    public function setUp(): void
    {
        $this->markdownService = $this->createMock(MarkdownService::class);
        $this->mentionService  = $this->createMock(CommentMentionService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $user = new User();
        $user->setId(123);
        $user->setName('Sherlock Holmes');
        $user->setEmail('sherlock@example.com');

        $request = $this->createMock(CommentPreviewRequest::class);
        $request->expects(self::once())->method('getMessage')->willReturn('message1');

        $this->mentionService->expects(self::once())->method('getMentionedUsers')->willReturn(['@user:123[Sherlock Holmes]' => $user]);
        $this->mentionService->expects(self::once())->method('replaceMentionedUsers')
            ->with('message1', ['@user:123[Sherlock Holmes]' => $user])
            ->willReturn('message2');

        $this->markdownService->expects(self::once())->method('convert')->with('message2')->willReturn('markdown');

        /** @var Response $response */
        $response = ($this->controller)($request);
        static::assertSame('markdown', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CommentPreviewController($this->mentionService, $this->markdownService);
    }
}
