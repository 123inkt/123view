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
}
