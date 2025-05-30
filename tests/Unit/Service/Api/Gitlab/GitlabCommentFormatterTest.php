<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\Api\Gitlab;

use DR\Review\Controller\App\Review\ReviewController;
use DR\Review\Entity\Review\CodeReview;
use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\Review\LineReference;
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
        $review = new CodeReview();
        $review->setProjectId(123);
        $comment = new Comment();
        $comment->setId(456);
        $comment->setLineReference(LineReference::fromString('old/path:new/path:1:2:3:commitSha:A'));
        $comment->setMessage("foo\nbar");
        $comment->setReview($review);

        $this->urlGenerator->expects($this->once())
            ->method('generate')
            ->with(ReviewController::class, ['review' => $review, 'filePath' => 'new/path'])
            ->willReturn('/path/to/review');

        static::assertSame(
            "foo\nbar<br>\n<br>\n*[123view: CR-123](https://example.com/path/to/review#focus:comment:456)*",
            $this->formatter->format($comment)
        );
    }
}
