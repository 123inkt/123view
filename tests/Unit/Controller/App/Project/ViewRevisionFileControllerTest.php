<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Project;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Project\ViewRevisionFileController;
use DR\Review\Entity\Revision\Revision;
use DR\Review\Service\Git\Show\LockableGitShowService;
use DR\Review\Tests\AbstractControllerTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

#[CoversClass(ViewRevisionFileController::class)]
class ViewRevisionFileControllerTest extends AbstractControllerTestCase
{
    private LockableGitShowService&MockObject $showService;

    protected function setUp(): void
    {
        $this->showService = $this->createMock(LockableGitShowService::class);
        parent::setUp();
    }

    public function testInvoke(): void
    {
        $request  = new Request(['file' => 'image.jpg']);
        $revision = new Revision();

        $this->showService->expects(self::once())->method('getFileContents')->willReturn('contents');

        $response = ($this->controller)($request, $revision);
        self::assertSame('contents', $response->getContent());
        self::assertSame(200, $response->getStatusCode());
        self::assertSame('image/jpg', $response->headers->get('Content-Type'));
        self::assertSame('public', $response->headers->get('Cache-Control'));
    }

    public function testInvokeInvalidMimetype(): void
    {
        $request  = new Request(['file' => 'text/plain']);
        $revision = new Revision();

        $this->showService->expects(self::never())->method('getFileContents');

        $this->expectException(BadRequestHttpException::class);
        $this->expectExceptionMessage('Could not determine mime-type for file "text/plain"');
        ($this->controller)($request, $revision);
    }

    public function getController(): AbstractController
    {
        return new ViewRevisionFileController($this->showService);
    }
}
