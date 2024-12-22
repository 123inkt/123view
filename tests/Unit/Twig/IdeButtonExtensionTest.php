<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Service\User\IdeUrlPatternProvider;
use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\IdeButtonExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\TwigFunction;

#[CoversClass(IdeButtonExtension::class)]
class IdeButtonExtensionTest extends AbstractTestCase
{
    private IdeUrlPatternProvider&MockObject $ideUrlPatternProvider;
    private Environment&MockObject           $twig;
    private IdeButtonExtension               $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->ideUrlPatternProvider = $this->createMock(IdeUrlPatternProvider::class);
        $this->twig                  = $this->createMock(Environment::class);

        $this->extension = new IdeButtonExtension(true, 'title', $this->ideUrlPatternProvider, $this->twig);
    }

    public function testGetFunctions(): void
    {
        $expected = [new TwigFunction('ide_button', [$this->extension, 'createLink'], ['is_safe' => ['all']])];
        static::assertEquals($expected, $this->extension->getFunctions());
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function testCreateLink(): void
    {
        $this->ideUrlPatternProvider->expects(self::once())->method('getUrl')->willReturn('url {file} {line}');
        $this->twig->expects(static::once())->method('render')->with(
            '/extension/ide-button.widget.html.twig',
            [
                'url' => 'url file 123',
                'title' => 'title'
            ]
        )->willReturn('html');

        static::assertSame('html', $this->extension->createLink('file', 123));
    }

    /**
     * @throws SyntaxError|RuntimeError|LoaderError
     */
    public function testCreateLinkDisabled(): void
    {
        $extension = new IdeButtonExtension(false, 'title', $this->ideUrlPatternProvider, $this->twig);

        $this->ideUrlPatternProvider->expects(self::never())->method('getUrl');
        $this->twig->expects(static::never())->method('render');

        static::assertSame('', $extension->createLink('file', 123));
    }
}
