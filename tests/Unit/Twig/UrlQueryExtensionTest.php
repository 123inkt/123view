<?php
declare(strict_types=1);

namespace DR\Review\Tests\Unit\Twig;

use DR\Review\Tests\AbstractTestCase;
use DR\Review\Twig\UrlQueryExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

#[CoversClass(UrlQueryExtension::class)]
class UrlQueryExtensionTest extends AbstractTestCase
{
    public function testGetUrlQueryWithoutParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=bar', $extension->getUrlQuery([]));
    }

    public function testGetUrlWithoutRequest(): void
    {
        $extension = new UrlQueryExtension(new RequestStack());
        static::assertSame('?foo=bar', $extension->getUrlQuery(['foo' => 'bar']));
    }

    public function testGetUrlQueryReplaceParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=replaced', $extension->getUrlQuery(['foo' => 'replaced']));
    }

    public function testGetUrlQueryAppendParams(): void
    {
        $request   = new Request(['foo' => 'bar']);
        $extension = new UrlQueryExtension($this->createRequestStack($request));
        static::assertSame('?foo=bar&sherlock=holmes', $extension->getUrlQuery(['sherlock' => 'holmes']));
    }

    private function createRequestStack(?Request $request = null): RequestStack
    {
        return new RequestStack([$request ?? new Request()]);
    }
}
