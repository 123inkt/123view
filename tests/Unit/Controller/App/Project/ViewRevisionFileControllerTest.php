<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ViewRevisionFileController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Service\Markdown\MarkdownConverterService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;

/**
 * @extends AbstractControllerTestCase<ViewRevisionFileController>
 */
#[CoversClass(ViewRevisionFileController::class)]
class ViewRevisionFileControllerTest extends AbstractControllerTestCase
{
    private LockableGitShowService&MockObject   $showService;
    private MarkdownConverterService&MockObject $converter;

    protected function setUp(): void
    {
        $this->showService = $this->createMock(LockableGitShowService::class);
        $this->converter   = $this->createMock(MarkdownConverterService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request  = new Request(['file' => 'image.jpg']);
        $revision = new Revision();

        $this->showService->expects($this->once())->method('getFileContents')->with($revision, 'image.jpg', true)->willReturn('contents');

        $response = ($this->controller)($request, $revision);
        static::assertSame('contents', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('image/jpeg', $response->headers->get('Content-Type'));
        static::assertSame('public', $response->headers->get('Cache-Control'));
    }

    public function testInvokeWithMarkdown(): void
    {
        $request  = new Request(['file' => 'readme.md']);
        $revision = new Revision();

        $this->showService->expects($this->once())->method('getFileContents')->with($revision, 'readme.md', true)->willReturn('markdown');
        $this->converter->expects($this->once())->method('convert')->with('markdown')->willReturn('html');

        $response = ($this->controller)($request, $revision);
        static::assertSame('html', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('text/html', $response->headers->get('Content-Type'));
    }

    public function testInvokeInvalidMimetype(): void
    {
        $request  = new Request(['file' => 'readme.cmd']);
        $revision = new Revision();

        $this->showService->expects($this->once())->method('getFileContents')->with($revision, 'readme.cmd', true)->willReturn('text');

        $response = ($this->controller)($request, $revision);
        static::assertSame('text', $response->getContent());
        static::assertSame(200, $response->getStatusCode());
        static::assertSame('text/plain', $response->headers->get('Content-Type'));
    }

    public function getController(): AbstractController
    {
        return new ViewRevisionFileController($this->showService, $this->converter);
    }
}
