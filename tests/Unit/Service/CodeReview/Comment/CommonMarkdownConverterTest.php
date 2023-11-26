<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Service\CodeReview\Comment\CommonMarkdownConverter;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use PHPUnit\Framework\Attributes\CoversClass;
use Spatie\CommonMarkHighlighter\FencedCodeRenderer;

#[CoversClass(CommonMarkdownConverter::class)]
class CommonMarkdownConverterTest extends AbstractTestCase
{
    public function testConstruct(): void
    {
        $converter   = new CommonMarkdownConverter();
        $environment = $converter->getEnvironment();

        $extension = $environment->getExtensions();
        static::assertCount(3, $extension);

        $renderers = [...$environment->getRenderersForClass(FencedCode::class)];
        static::assertCount(2, $renderers);
        static::assertInstanceOf(FencedCodeRenderer::class, $renderers[0]);
    }
}
