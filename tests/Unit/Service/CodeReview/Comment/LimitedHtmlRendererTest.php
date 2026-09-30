<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Service\CodeReview\Comment;

use DR\Review\Service\CodeReview\Comment\LimitedHtmlRenderer;
use DR\Review\Tests\AbstractTestCase;
use League\CommonMark\Extension\CommonMark\Node\Inline\HtmlInline;
use League\CommonMark\Renderer\ChildNodeRendererInterface;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LimitedHtmlRenderer::class)]
class LimitedHtmlRendererTest extends AbstractTestCase
{
    private LimitedHtmlRenderer $renderer;
    private ChildNodeRendererInterface $childRenderer;

    protected function setUp(): void
    {
        parent::setUp();
        $this->renderer      = new LimitedHtmlRenderer();
        $this->childRenderer = static::createStub(ChildNodeRendererInterface::class);
    }

    public function testAllowedHtmlIsPreserved(): void
    {
        $html = '<DETAILS>details</DETAILS> <summary>summary</summary> <!-- comment -->';

        static::assertSame($html, $this->renderer->render(new HtmlInline($html), $this->childRenderer));
    }

    public function testMixedHtmlIsEscapedAndPreserved(): void
    {
        static::assertSame(
            'text &lt;div&gt;content&lt;/div&gt; &lt;script&gt;alert(1)&lt;/script&gt; <details>details</details>',
            $this->renderer->render(
                new HtmlInline('text <div>content</div> <script>alert(1)</script> <details>details</details>'),
                $this->childRenderer
            )
        );
    }
}
