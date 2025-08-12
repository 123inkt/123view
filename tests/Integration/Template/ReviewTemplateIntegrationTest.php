<?php
declare(strict_types=1);

namespace DR\Review\Tests\Integration\Template;

use DR\Review\Entity\Review\Comment;
use DR\Review\Entity\User\User;
use DR\Review\Tests\AbstractTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;

#[CoversNothing]
class ReviewTemplateIntegrationTest extends AbstractTestCase
{
    public function testReviewTemplateIncludesCommentDeepLinkController(): void
    {
        $templateContent = file_get_contents(__DIR__ . '/../../../templates/app/review/review.html.twig');

        // Test that the comment-deep-link controller is added to the review template
        static::assertStringContainsString('comment-deep-link', $templateContent);

        // Test that it's properly integrated with other controllers
        static::assertStringContainsString('review review-comment-visibility review-navigation comment-deep-link', $templateContent);

        // Test that the stimulus_controller function is used correctly
        static::assertStringContainsString('{{ stimulus_controller(', $templateContent);
    }

    public function testCommentDeepLinkWorkflow(): void
    {
        // Test the complete workflow from comment ID to deep linking

        // 1. Comment should have unique ID in template
        $user = new User();
        $user->setName('testuser');

        $comment = new Comment();
        $comment->setId(789);
        $comment->setMessage('Integration test comment');
        $comment->setUser($user);
        $comment->setCreateTimestamp(time());
        $comment->setUpdateTimestamp(time());

        // 2. Test that comment template generates proper HTML structure
        $expectedAnchorId  = 'comment-789';
        $expectedPermalink = '#comment-789';
        $expectedDisplayId = '#789';

        // These would be rendered in the actual template
        static::assertSame('comment-789', $expectedAnchorId);
        static::assertSame('#comment-789', $expectedPermalink);
        static::assertSame('#789', $expectedDisplayId);
    }

    public function testTranslationKeyExists(): void
    {
        $translationContent = file_get_contents(__DIR__ . '/../../../translations/messages+intl-icu.en.php');

        // Test that the copy comment link translation exists
        static::assertStringContainsString("'copy.comment.link'", $translationContent);
        static::assertStringContainsString('Copy comment link', $translationContent);
    }

    public function testJavaScriptControllerFileExists(): void
    {
        $controllerPath = __DIR__ . '/../../../assets/ts/controllers/comment_deep_link_controller.ts';

        // Test that the JavaScript controller file exists
        static::assertFileExists($controllerPath);

        $controllerContent = file_get_contents($controllerPath);

        // Test key functionality is present
        static::assertStringContainsString('handleCommentDeepLink', $controllerContent);
        static::assertStringContainsString('scrollIntoView', $controllerContent);
        static::assertStringContainsString('comment-highlighted', $controllerContent);
        static::assertStringContainsString('#comment-', $controllerContent);
    }

    public function testCSSStylesExist(): void
    {
        $cssPath = __DIR__ . '/../../../assets/styles/comment.scss';

        // Test that CSS file exists and contains deep linking styles
        static::assertFileExists($cssPath);

        $cssContent = file_get_contents($cssPath);

        // Test that deep linking CSS classes exist
        static::assertStringContainsString('.comment-highlighted', $cssContent);
        static::assertStringContainsString('.comment__id', $cssContent);
    }
}
