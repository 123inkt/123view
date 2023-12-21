<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Service\Api\Gitlab\GitlabCommentFormatter;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

#[CoversClass(GitlabCommentFormatter::class)]
class GitlabCommentFormatterTest extends AbstractTestCase
{
    private UrlGeneratorInterface&MockObject $urlGenerator;
    private GitlabCommentFormatter           $formatter;

    protected function setUp(): void
    {
        parent::setUp();
        $this->urlGenerator = $this->createMock(UrlGeneratorInterface::class);
        $this->formatter    = new GitlabCommentFormatter('https://example.com', $this->urlGenerator);
    }

    public function testFormat(): void
    {
        $review  = new CodeReview();
        $comment = new Comment();
        $comment->setMessage("foo\nbar");
        $comment->setReview($review);

        $this->urlGenerator->expects(self::once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review])
            ->willReturn('/path/to/review');

        static::assertSame("foo\n<br>bar\n<br>\n<br>*123view: https://example.com/path/to/review*", $this->formatter->format($comment));
    }
}
