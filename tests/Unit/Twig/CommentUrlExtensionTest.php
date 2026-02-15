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
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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

    #[TestWith([true, 'https://example.com/review/456/file/src/Service/TestService.php', UrlGeneratorInterface::ABSOLUTE_URL])]
    #[TestWith([false, '/review/789/file/tests/Unit/TestTest.php', UrlGeneratorInterface::ABSOLUTE_PATH])]
    public function testGetCommentUrl(bool $absolute, string $expectedUrl, int $expectedReferenceType): void
    {
        $review   = static::createStub(CodeReview::class);
        $comment  = $this->createMock(Comment::class);
        $filePath = 'src/Service/TestService.php';

        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                $expectedReferenceType
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentUrl($comment, $absolute);

        static::assertSame($expectedUrl, $result);
    }

    #[TestWith([true, 'https://example.com/review/222/file/config/services.yaml', UrlGeneratorInterface::ABSOLUTE_URL])]
    #[TestWith([false, '/review/333/file/assets/ts/controllers/test_controller.ts', UrlGeneratorInterface::ABSOLUTE_PATH])]
    public function testGetCommentReplyUrl(bool $absolute, string $expectedUrl, int $expectedReferenceType): void
    {
        $review       = static::createStub(CodeReview::class);
        $comment      = $this->createMock(Comment::class);
        $commentReply = $this->createMock(CommentReply::class);
        $filePath     = 'config/services.yaml';

        $commentReply->expects($this->once())->method('getComment')->willReturn($comment);
        $comment->expects($this->once())->method('getReview')->willReturn($review);
        $comment->expects($this->once())->method('getFilePath')->willReturn($filePath);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(
                ReviewController::class,
                ['review' => $review, 'filePath' => $filePath],
                $expectedReferenceType
            )
            ->willReturn($expectedUrl);

        $result = $this->extension->getCommentReplyUrl($commentReply, $absolute);

        static::assertSame($expectedUrl, $result);
    }
}
