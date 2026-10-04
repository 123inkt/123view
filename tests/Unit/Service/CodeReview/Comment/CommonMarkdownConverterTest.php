<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Service\CodeReview\Comment\CommonMarkdownConverter;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Extension\CommonMark\Node\Block\FencedCode;
use PHPUnit\Framework\Attributes\CoversClass;
use Psr\EventDispatcher\EventDispatcherInterface;
use Tempest\Highlight\CommonMark\CodeBlockRenderer;
use Tempest\Highlight\CommonMark\HighlightExtension;

#[CoversClass(CommonMarkdownConverter::class)]
class CommonMarkdownConverterTest extends AbstractTestCase
{
    private CommonMarkdownConverter $converter;

    protected function setUp(): void
    {
        parent::setUp();
        $eventDispatcher = static::createStub(EventDispatcherInterface::class);
        $eventDispatcher->method('dispatch')->willReturnArgument(0);
        $this->converter = new CommonMarkdownConverter($eventDispatcher);
    }

    public function testConstruct(): void
    {
        $environment = $this->converter->getEnvironment();

        $extensions = [...$environment->getExtensions()];
        static::assertCount(4, $extensions);
        static::assertInstanceOf(HighlightExtension::class, $extensions[3]);

        $renderers = [...$environment->getRenderersForClass(FencedCode::class)];
        static::assertCount(2, $renderers);
        static::assertInstanceOf(CodeBlockRenderer::class, $renderers[0]);
    }

    public function testAllowedHtml(): void
    {
        static::assertSame(
            "<details>details</details> <summary>summary</summary> <sub>sub</sub> <!-- comment -->\n",
            $this->converter->convert('<details>details</details> <summary>summary</summary> <sub>sub</sub> <!-- comment -->')->getContent()
        );
    }

    public function testDisallowedHtmlIsEscaped(): void
    {
        static::assertSame(
            "<p>text &lt;div&gt;content&lt;/div&gt; &lt;script&gt;alert(1)&lt;/script&gt; <code>&lt;details&gt;</code></p>\n",
            $this->converter->convert('text <div>content</div> <script>alert(1)</script> `<details>`')->getContent()
        );
    }
}
