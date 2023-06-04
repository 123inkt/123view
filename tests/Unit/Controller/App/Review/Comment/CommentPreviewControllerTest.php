<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Controller\App\Review\Comment;

use DR\Review\Controller\AbstractController;
use DR\Review\Controller\App\Review\Comment\CommentPreviewController;
use DR\Review\Entity\User\User;
use DR\Review\Request\Comment\CommentPreviewRequest;
use DR\Review\Service\CodeReview\Comment\CommentMentionService;
use DR\Review\Tests\AbstractControllerTestCase;
use League\CommonMark\MarkdownConverter;
use League\CommonMark\Output\RenderedContentInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\HttpFoundation\Response;
use function PHPUnit\Framework\once;

/**
 * @coversDefaultClass \DR\Review\Controller\App\Review\Comment\CommentPreviewController
 * @covers ::__construct
 */
class CommentPreviewControllerTest extends AbstractControllerTestCase
{
    private MarkdownConverter&MockObject     $markdownService;
    private CommentMentionService&MockObject $mentionService;

    protected function setUp(): void
    {
        $this->markdownService = $this->createMock(MarkdownConverter::class);
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

        $renderedContent = $this->createMock(RenderedContentInterface::class);
        $renderedContent->expects(once())->method('getContent')->willReturn('markdown');
        $this->markdownService->expects(self::once())->method('convert')->with('message2')->willReturn($renderedContent);

        /** @var Response $response */
        $response = ($this->controller)($request);
        static::assertSame('markdown', $response->getContent());
    }

    public function getController(): AbstractController
    {
        return new CommentPreviewController($this->mentionService, $this->markdownService);
    }
}
