<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Styling;

use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CommentStylingTest extends AbstractTestCase
{
    private string $cssContent;

    public function setUp(): void
    {
        parent::setUp();
        $cssPath          = __DIR__ . '/../../../assets/styles/comment.scss';
        $this->cssContent = file_get_contents($cssPath);
    }

    public function testCommentHighlightStylingExists(): void
    {
        // Test that comment highlighting CSS class exists
        static::assertStringContainsString('.comment-highlighted', $this->cssContent);

        // Test that highlighting has proper background color
        static::assertStringContainsString('background:', $this->cssContent);
        static::assertStringContainsString('--bs-warning-bg-subtle', $this->cssContent);

        // Test that highlighting has border styling
        static::assertStringContainsString('border-left:', $this->cssContent);
        static::assertStringContainsString('--bs-warning', $this->cssContent);
    }

    public function testCommentIdStylingExists(): void
    {
        // Test that comment ID styling exists
        static::assertStringContainsString('.comment__id', $this->cssContent);

        // Test that comment ID has proper font size
        static::assertStringContainsString('font-size: 0.75rem', $this->cssContent);
    }

    public function testCommentHighlightTransitions(): void
    {
        // Test that transitions are defined for smooth highlighting
        static::assertStringContainsString('transition:', $this->cssContent);
        static::assertStringContainsString('background-color', $this->cssContent);
        static::assertStringContainsString('border-left', $this->cssContent);
    }

    public function testResolvedCommentHighlighting(): void
    {
        // Test that resolved comments have different highlighting
        static::assertStringContainsString('&.comment__state_resolved', $this->cssContent);
        static::assertStringContainsString('--bs-success-bg-subtle', $this->cssContent);
        static::assertStringContainsString('--bs-success', $this->cssContent);
    }

    public function testCommentIdLinkStyling(): void
    {
        // Test that comment ID links have hover effects
        static::assertStringContainsString('opacity: 0.6', $this->cssContent);
        static::assertStringContainsString('&:hover', $this->cssContent);
        static::assertStringContainsString('opacity: 1', $this->cssContent);
    }
}
