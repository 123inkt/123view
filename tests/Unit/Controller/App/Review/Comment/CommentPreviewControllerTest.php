<?php
declare(strict_types=1);

namespace DR\GitCommitNotification\Tests\Unit\Controller\App\Review\Comment;

use DR\GitCommitNotification\Controller\AbstractController;
use DR\GitCommitNotification\Controller\App\Review\Comment\CommentPreviewController;
use DR\GitCommitNotification\Request\Comment\CommentPreviewRequest;
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
    private MarkdownService&MockObject $markdownService;

    public function setUp(): void
    {
        $this->markdownService = $this->createMock(MarkdownService::class);
        parent::setUp();
    }

    /**
     * @covers ::__invoke
     */
    public function testInvoke(): void
    {
        $request = $this->createMock(CommentPreviewRequest::class);
        $request->expects(self::once())->method('getMessage')->willReturn('message');
        $this->markdownService->expects(self::once())->method('convert')->with('message')->willReturn('markdown');

        /** @var Response $response */
        $response = ($this->controller)($request);
        static::assertSame('markdown', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CommentPreviewController($this->markdownService);
    }
}
