<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Template;

use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class CommentTemplateTest extends AbstractTestCase
{
    public function testCommentTemplateContainsDeepLinkingElements(): void
    {
        $templatePath    = __DIR__ . '/../../../templates/app/review/comment/comment.html.twig';
        $templateContent = file_get_contents($templatePath);

        // Test that template contains comment ID anchor
        static::assertStringContainsString('id="comment-{{ comment.id }}"', $templateContent);

        // Test that template contains comment permalink
        static::assertStringContainsString('#comment-{{ comment.id }}', $templateContent);

        // Test that template contains comment ID display
        static::assertStringContainsString('#{{ comment.id }}', $templateContent);

        // Test that template contains clipboard functionality
        static::assertStringContainsString('navigator.clipboard.writeText', $templateContent);

        // Test that template contains link icon
        static::assertStringContainsString('bi-link-45deg', $templateContent);
    }

    public function testCommentTemplateHasAccessibilityFeatures(): void
    {
        $templatePath    = __DIR__ . '/../../../templates/app/review/comment/comment.html.twig';
        $templateContent = file_get_contents($templatePath);

        // Test that template has title attribute for accessibility
        static::assertStringContainsString('title="{{ \'copy.comment.link\'|trans }}"', $templateContent);

        // Test that template has proper link structure
        static::assertStringContainsString('<a href="#comment-{{ comment.id }}"', $templateContent);
    }

    public function testCommentTemplateHasProperStyling(): void
    {
        $templatePath    = __DIR__ . '/../../../templates/app/review/comment/comment.html.twig';
        $templateContent = file_get_contents($templatePath);

        // Test that template has comment ID styling class
        static::assertStringContainsString('comment__id', $templateContent);

        // Test that template has proper spacing classes
        static::assertStringContainsString('ms-2', $templateContent);

        // Test that template has text styling
        static::assertStringContainsString('text-muted', $templateContent);
    }

    public function testCommentTemplateIntegration(): void
    {
        $templatePath    = __DIR__ . '/../../../templates/app/review/comment/comment.html.twig';
        $templateContent = file_get_contents($templatePath);

        // Test that the deep linking code is properly placed between author and tags
        $authorPattern = '/comment__author.*?comment__id.*?comment\.tag/s';
        static::assertMatchesRegularExpression($authorPattern, $templateContent);
    }
}
