<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\CommentReply;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\CommentUrlExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;

#[CoversClass(CommentUrlExtension::class)]
class CommentUrlExtensionTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private CommentUrlExtension              $extension;

    public function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->extension    = new CommentUrlExtension($this->urlGenerator);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        static::assertCount(2, $functions);
        static::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);

        // Check first function (comment_url)
        $commentUrlFunction = $functions[0];
        static::assertSame('comment_url', $commentUrlFunction->getName());
        static::assertSame([$this->extension, 'getCommentUrl'], $commentUrlFunction->getCallable());

        // Check second function (comment_reply_url)
        $commentReplyUrlFunction = $functions[1];
        static::assertSame('comment_reply_url', $commentReplyUrlFunction->getName());
        static::assertSame([$this->extension, 'getCommentReplyUrl'], $commentReplyUrlFunction->getCallable());
    }

    public function testGetCommentUrlWithAbsoluteTrue(): void
    {
        $review      = $this->createMock(CodeReview::class);
        $comment     = $this->createMock(Comment::class);
        $filePath    = 'src/Service/TestService.php';
        $expectedUrl = 'https://example.com/review/456/file/src/Service/TestService.php';

        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentUrl($comment, true);

        static::assertSame($expectedUrl, $result);
    }

    public function testGetCommentUrlWithAbsoluteFalse(): void
    {
        $review      = $this->createMock(CodeReview::class);
        $comment     = $this->createMock(Comment::class);
        $filePath    = 'tests/Unit/TestTest.php';
        $expectedUrl = '/review/789/file/tests/Unit/TestTest.php';

        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentUrl($comment);

        static::assertSame($expectedUrl, $result);
    }

    public function testGetCommentReplyUrlWithAbsoluteTrue(): void
    {
        $review       = $this->createMock(CodeReview::class);
        $comment      = $this->createMock(Comment::class);
        $commentReply = $this->createMock(CommentReply::class);
        $filePath     = 'config/services.yaml';
        $expectedUrl  = 'https://example.com/review/222/file/config/services.yaml';

        $commentReply->expects($this->once())->method('getComment')->willReturn($comment);
        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                UrlGeneratorInterface::ABSOLUTE_URL
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentReplyUrl($commentReply, true);

        static::assertSame($expectedUrl, $result);
    }

    public function testGetCommentReplyUrlWithAbsoluteFalse(): void
    {
        $review       = $this->createMock(CodeReview::class);
        $comment      = $this->createMock(Comment::class);
        $commentReply = $this->createMock(CommentReply::class);
        $filePath     = 'assets/ts/controllers/test_controller.ts';
        $expectedUrl  = '/review/333/file/assets/ts/controllers/test_controller.ts';

        $commentReply->expects($this->once())->method('getComment')->willReturn($comment);
        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                UrlGeneratorInterface::ABSOLUTE_PATH
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentReplyUrl($commentReply);

        static::assertSame($expectedUrl, $result);
    }
}
