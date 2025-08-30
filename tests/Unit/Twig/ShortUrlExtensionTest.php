<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Service\Url\ShortUrlGenerator;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\ShortUrlExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\TwigFunction;

#[CoversClass(ShortUrlExtension::class)]
class ShortUrlExtensionTest extends AbstractTestCase
{
    private ShortUrlGenerator&MockObject $shortUrlGenerator;
    private ShortUrlExtension            $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->shortUrlGenerator = $this->createMock(ShortUrlGenerator::class);
        $this->extension         = new ShortUrlExtension($this->shortUrlGenerator);
    }

    public function testGetFunctions(): void
    {
        $functions = $this->extension->getFunctions();

        static::assertCount(1, $functions);
        static::assertContainsOnlyInstancesOf(TwigFunction::class, $functions);

        $shortUrlFunction = $functions[0];
        static::assertSame('short_url', $shortUrlFunction->getName());
    }

    public function testGenerateShortUrlWithDefaults(): void
    {
        $routeName        = 'app_review';
        $expectedShortUrl = '/url/abc123';

        $this->shortUrlGenerator->expects($this->once())
            ->method('generate')
            ->with($routeName, ['foo' => 'bar'], UrlGeneratorInterface::ABSOLUTE_URL)
            ->willReturn($expectedShortUrl);

        $result = $this->extension->generateShortUrl($routeName, ['foo' => 'bar']);

        static::assertSame($expectedShortUrl, $result);
    }
}
