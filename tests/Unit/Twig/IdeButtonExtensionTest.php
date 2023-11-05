<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

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
    private Environment&MockObject $twig;
    private IdeButtonExtension     $extension;

    protected function setUp(): void
    {
        parent::setUp();
        $this->twig      = $this->createMock(Environment::class);
        $this->extension = new IdeButtonExtension(true, 'url {file} {line}', 'title', $this->twig);
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
        $this->twig->expects(static::once())->method('render')->with(
            '/extension/ide-button.widget.html.twig',
            [
                'url'   => 'url file 123',
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
        $extension = new IdeButtonExtension(false, 'url {file} {line}', 'title', $this->twig);

        $this->twig->expects(static::never())->method('render');

        static::assertSame('', $extension->createLink('file', 123));
    }
}
